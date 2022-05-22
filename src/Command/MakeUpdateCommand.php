<?php

namespace Kodmit\MessengerCqrsGeneratorBundle\Command;

use Kodmit\MessengerCqrsGeneratorBundle\ClassFinder;

use Kodmit\MessengerCqrsGeneratorBundle\Generator\AbstractGenerator;
use Kodmit\MessengerCqrsGeneratorBundle\Generator\Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeUpdateCommand extends Command
{
    protected static $defaultName = 'kodmit:make:update';
    protected static $defaultDescription = 'Creates a delete DTO, handler and hydrate the existing controller';

    private ClassFinder $classFinder;

    public function __construct()
    {
        $this->classFinder = new ClassFinder();
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('entity', InputArgument::OPTIONAL, 'The entity namespace and name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entity = $input->getArgument('entity');

        $entities = ClassFinder::getClassesInNamespace('App\Entity');

        if (null !== $entity && false === in_array($entity, $entities)) {
            $io->error(sprintf('Entity "%s" not found', $entity));
            return Command::FAILURE;
        }

        if (null === $entity) {
            $entity = $io->choice('Choose an entity', $entities);
        }

        $io->text(sprintf('Generating REST CRUD for entity "%s"...', $entity));

        $generator = new Generator($entity);
        $generatedFiles = $generator->generatedSpecificScope(AbstractGenerator::UPDATE);

        $io->newLine();

        $io->write('Files generated:');
        $io->listing($generatedFiles);

        $io->success('Messenger update logic and controller generated, now add your own logic :)');

        return Command::SUCCESS;
    }
}
