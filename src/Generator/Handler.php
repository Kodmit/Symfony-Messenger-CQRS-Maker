<?php

namespace Kod\MessengerCqrsGeneratorBundle\Generator;

use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class Handler extends AbstractGenerator implements GeneratorInterface
{
    public const CREATE = 'Create';
    public const DELETE = 'Delete';
    public const UPDATE = 'Update';

    private const AVAILABLE_SCOPES = [self::CREATE, self::DELETE, self::UPDATE];

    public function generate(string $scope = null): void
    {
        if (null === $scope) {
            foreach (self::AVAILABLE_SCOPES as $availableScope) {
                $this->createFile($availableScope);
            }
            return;
        }

        if (false === in_array($scope, self::AVAILABLE_SCOPES)) {
            throw new \DomainException(sprintf('invalid scope "%s"', $scope));
        }

        $this->createFile($scope);
    }

    private function createFile(string $scope): void
    {
        $filePath = sprintf('%s/%s%sHandler.php', $this->dirPath, $scope, $this->className);
        touch($filePath);

        $returnType = $this->className;

        if (self::DELETE === $scope) {
            $returnType = 'void';
        }

        $searchEntityLogic = '';

        if (self::CREATE !== $scope) {
            $searchEntityLogic = sprintf('
        $%1$s = $this->%1$sRepository->find($command->get%2$sId());
           
        if (null === $%1$s) {
            throw new \DomainException(sprintf(\'%1$s with id "%%s" not found\', $command->get%2$sId()));
        }
        ', strtolower($this->className), $this->className);
        }


        $data = sprintf('<?php
        
namespace App\\Action\\%1$s;

use App\Repository\%1$sRepository;
use App\Entity\%1$s;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class %2$s%1$sHandler implements MessageHandlerInterface
{
    private %1$sRepository $%3$sRepository;
    private EntityManagerInterface $entityManager;
    
    public function __construct(%1$sRepository $%3$sRepository, EntityManagerInterface $entityManager)
    {
        $this->%3$sRepository = $%3$sRepository;
        $this->entityManager = $entityManager;
    }
    
    public function __invoke(%2$s%1$s $command): %4$s
    {
%5$s

        // TODO: Implement your own logic :)
    }
}
',
            $this->className,
            $scope,
            strtolower($this->className),
            $returnType,
            $searchEntityLogic
        );

        file_put_contents($filePath, $data);
    }
}