<?php

use Knp\Bundle\RadBundle\AppKernel;

AppKernel::$organization = 'Test';

$bundles = array(
    new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
    new Symfony\Bundle\SecurityBundle\SecurityBundle(),
    new Symfony\Bundle\TwigBundle\TwigBundle(),
    new Symfony\Bundle\MonologBundle\MonologBundle(),
    new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
    new Symfony\Bundle\AsseticBundle\AsseticBundle(),

    new Knp\Bundle\RadBundle\KnpRadBundle(),

    //new Test\BlogBundle\TestBlogBundle(),
    new Knp\Bundle\RadBundle\Bundle\ConventionalBundle($kernel, 'BlogBundle')
);

if (in_array($kernel->getEnvironment(), array('dev', 'test'))) {
    $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
    $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
}

return $bundles;
