<?php

namespace Knp\Bundle\RadBundle\Extension;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConventionalExtension extends Extension
{
    private $alias;
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Build the extension services
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        if (file_exists($services = $this->path.'/config/services.xml')) {
            $loader = new XmlFileLoader($container, new FileLocator($this->path.'/config'));
            $loader->load($services);
        }

        if (file_exists($services = $this->path.'/config/services.yml')) {
            $loader = new YamlFileLoader($container, new FileLocator($this->path.'/config'));
            $loader->load($services);
        }

        foreach ($configs as $config) {
            foreach ($config as $key => $val) {
                $container->setParameter($this->getAlias().'.'.$key, $val);
            }
        }
    }

    public function getAlias()
    {
        if (null === $this->alias) {
            $this->alias = strtolower(preg_replace('/Bundle$/', '', basename($this->path)));
        }

        return $this->alias;
    }
}
