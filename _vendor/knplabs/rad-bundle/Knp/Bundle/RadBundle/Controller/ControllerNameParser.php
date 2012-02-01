<?php

namespace Knp\Bundle\RadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser as BaseNameParser;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;

class ControllerNameParser extends BaseNameParser
{
    public function parse($controller)
    {
        if (1 == substr_count($controller, ':')) {
            $controller = $this->kernel->getConfiguration()->getApplicationName().':'.$controller;
        }

        $parsed = parent::parse($controller);
        list($bundle,,) = explode(':', $controller);

        if ($this->kernel->getBundle($bundle) instanceof ApplicationBundle) {
            $parts = explode('::', $parsed);

            if (method_exists($parts[0], $parts[1])) {
                return $parsed;
            }

            return preg_replace('/Action$/', '', $parsed);
        }

        return $parsed;
    }
}
