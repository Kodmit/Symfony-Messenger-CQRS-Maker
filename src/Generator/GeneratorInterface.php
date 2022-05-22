<?php


namespace Kodmit\MessengerCqrsGeneratorBundle\Generator;


interface GeneratorInterface
{
    public function generate(string $scope = null): array;
}