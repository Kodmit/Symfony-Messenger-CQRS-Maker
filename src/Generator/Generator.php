<?php

namespace Kod\MessengerCqrsGeneratorBundle\Generator;

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

    public function generateCrud(): void
    {
        $this->dto->generate();
        $this->handler->generate();
        $this->controller->generate();
    }
}