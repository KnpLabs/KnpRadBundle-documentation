<?php

namespace Knp\Bundle\RadBundle\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseTestCase;

abstract class WebTestCase extends BaseTestCase
{
    static protected function getKernelClass()
    {
        return isset($_SERVER['KERNEL_CLASS'])
            ? $_SERVER['KERNEL_CLASS']
            : 'Knp\Bundle\RadBundle\HttpKernel\RadKernel';
    }
}
