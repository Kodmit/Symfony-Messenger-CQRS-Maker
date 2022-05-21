<?php

namespace Kodmit\MessengerCqrsGeneratorBundle\Command;

use App\Kod\ClassFinder;
use App\Kod\Generator\Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CrudCreateCommand extends Command
{
    protected static $defaultName = 'kodmit:crud:create';
    protected static $defaultDescription = 'Creates a CRUD';

    private ClassFinder $classFinder;

    public function __construct(ClassFinder $classFinder)
    {
        $this->classFinder = $classFinder;
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
        $generator->generateCrud();

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}