<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\Manipulator;

use Symfony\Component\HttpKernel\KernelInterface;
use Sensio\Bundle\GeneratorBundle\Manipulator\Manipulator;

/**
 * Changes the PHP code of a Kernel.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerManipulator extends Manipulator
{
    private $controller;
    private $reflected;

    /**
     * Constructor.
     *
     */
    public function __construct($controller)
    {
        $this->controller = $controller;
        $this->reflected = new \ReflectionClass($controller);
    }

    /**
     * Adds an action at the end of the existing ones.
     *
     * @param string $action The method code
     *
     * @return Boolean true if it worked, false otherwise
     *
     */
    public function addAction($action)
    {
        if (!$this->reflected->getFilename()) {
            return false;
        }

        if ($this->reflected->hasMethod($action)) {
            return false;
        }

        $src = file($this->reflected->getFilename());
        $line = $this->reflected->getEndLine() - 1;

        $newSrc = array_replace($src, array($line => $action.' }'));

        file_put_contents($this->reflected->getFilename(), implode('', $newSrc));

        return true;
    }
}
