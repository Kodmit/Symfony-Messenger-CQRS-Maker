<?php

namespace Kodmit\MessengerCqrsGeneratorBundle\Generator;

class Generator
{
    private Controller $controller;
    private DTO $dto;
    private Handler $handler;

    public function __construct(string $namespace)
    {
        $this->controller = new Controller($namespace);
        $this->dto = new DTO($namespace);
        $this->handler = new Handler($namespace);
    }

    public function generateCrud(): array
    {
        return array_merge(
            $this->dto->generate(),
            $this->handler->generate(),
            $this->controller->generate()
        );
    }

    public function generatedSpecificScope(string $scope): array
    {
        if (false === in_array($scope, AbstractGenerator::AVAILABLE_SCOPES)) {
            throw new \LogicException(sprintf('invalid scope "%s"', $scope));
        }

        return array_merge(
            $this->dto->generate($scope),
            $this->handler->generate($scope),
            $this->controller->generate($scope)
        );
    }
}