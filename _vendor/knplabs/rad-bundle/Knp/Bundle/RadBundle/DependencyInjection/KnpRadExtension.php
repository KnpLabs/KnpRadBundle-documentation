<?php

namespace Knp\Bundle\RadBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;

class KnpRadExtension extends Extension
{
    /**
     * Build the extension services
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('routing.xml');
        $loader->load('controller.xml');
        $loader->load('templating.xml');
        $loader->load('view.xml');
        $loader->load('doctrine.xml');
    }
}
