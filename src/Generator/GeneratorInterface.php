<?php


namespace Kodmit\MessengerCqrsGeneratorBundle\Generator;


interface GeneratorInterface
{
    public function generate(): array;
}