<?php

namespace Knp\Bundle\RadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser as BaseNameParser;

use Knp\Bundle\RadBundle\Bundle\ConventionalBundle;

class ControllerNameParser extends BaseNameParser
{
    public function parse($controller)
    {
        if (3 != count($parts = explode(':', $controller))) {
            throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid a:b:c controller string.', $controller));
        }

        list($bundle, $class, $method) = $parts;

        $controller = parent::parse($controller);

        if ($this->kernel->getBundle($bundle) instanceof ConventionalBundle) {
            return preg_replace('/Action$/', '', $controller);
        }

        return $controller;
    }
}
