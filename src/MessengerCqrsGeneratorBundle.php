<?php

namespace Kodmit\MessengerCqrsGeneratorBundle;

use Kodmit\MessengerCqrsGeneratorBundle\DependencyInjection\MessengerCqrsGeneratorExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MessengerCqrsGeneratorBundle extends Bundle
{
    public function getContainerExtension(): MessengerCqrsGeneratorExtension
    {
        return new MessengerCqrsGeneratorExtension();
    }
}