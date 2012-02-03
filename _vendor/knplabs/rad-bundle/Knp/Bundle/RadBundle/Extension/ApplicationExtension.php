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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Finder\Finder;

/**
 * Application extension automatically loads service
 * definitions from application bundle.
 */
class ApplicationExtension extends Extension
{
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
        $xmlLoader = $this->getXmlFileLoader($container);
        $ymlLoader = $this->getYamlFileLoader($container);

        if (file_exists($services = $this->path.'/config/services.xml')) {
            $xmlLoader->load($services);
        }
        if (file_exists($services = $this->path.'/config/services.yml')) {
            $ymlLoader->load($services);
        }

        if (is_dir($dir = $this->path.'/config/services')) {
            foreach (Finder::create()->files()->name('*.xml')->depth(0)->sortByName()->in($dir) as $file) {
                $xmlLoader->load($file);
            }
            foreach (Finder::create()->files()->name('*.yml')->depth(0)->sortByName()->in($dir) as $file) {
                $ymlLoader->load($file);
            }
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
        return 'app';
    }

    /**
     * Returns new container XmlFileLoader.
     *
     * @return XmlFileLoader
     */
    protected function getXmlFileLoader(ContainerBuilder $container)
    {
        return new XmlFileLoader($container, new FileLocator($this->path.'/config'));
    }

    /**
     * Returns new container YamlFileLoader.
     *
     * @return YamlFileLoader
     */
    protected function getYamlFileLoader(ContainerBuilder $container)
    {
        return new YamlFileLoader($container, new FileLocator($this->path.'/config'));
    }
}
