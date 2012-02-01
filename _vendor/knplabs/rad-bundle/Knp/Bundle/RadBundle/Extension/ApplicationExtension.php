<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\Extension;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;

/**
 * Application extension automatically loads service
 * definitions from application bundle.
 */
class ApplicationExtension extends Extension
{
    private $alias;
    private $path;

    /**
     * Initializes extension.
     *
     * @param string $path Extension path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Build the extension services
     *
     * @param array            $configs   Merged configuration array
     * @param ContainerBuilder $container Container builder
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

    /**
     * Returns extension alias (configuration name).
     *
     * @return string
     */
    public function getAlias()
    {
        if (null === $this->alias) {
            $this->alias = strtolower(basename($this->path));
        }

        return $this->alias;
    }
}
