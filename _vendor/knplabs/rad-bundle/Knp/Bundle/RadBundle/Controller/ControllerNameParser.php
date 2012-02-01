<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser as BaseNameParser;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;

/**
 * Extends Symfony2 ControllerNameParser with support
 * of application bundle short notation.
 */
class ControllerNameParser extends BaseNameParser
{
    /**
     * {@inheritdoc}
     */
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
