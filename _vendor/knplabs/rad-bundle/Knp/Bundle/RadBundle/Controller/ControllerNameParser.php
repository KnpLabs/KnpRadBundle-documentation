<?php

namespace Knp\Bundle\RadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser as BaseNameParser;

use Knp\Bundle\RadBundle\Bundle\ConventionalBundle;

class ControllerNameParser extends BaseNameParser
{
    public function parse($controller)
    {
        list($bundle, $class, $method) = explode(':', $controller);

        $controller = parent::parse($controller);

        if ($this->kernel->getBundle($bundle) instanceof ConventionalBundle) {
            return substr($controller, 0, -6);
        }

        return $controller;
    }
}
