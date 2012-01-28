<?php

namespace Knp\Bundle\RadBundle\HttpKernel;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

use Knp\Bundle\RadBundle\DependencyInjection\Loader\ArrayLoader;

class RadKernel extends Kernel
{
    private $configuration;

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);

        $this->configuration = new KernelConfiguration($this);
        $this->configuration->load();
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getRootDir()
    {
        return realpath(__DIR__.'/../../../../../../../app');
    }

    public function getConfigDir()
    {
        return $this->getRootDir().'/config';
    }

    public function getProjectDir()
    {
        return realpath($this->getRootDir().'/../');
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/waste/logs';
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/waste/cache/'.$this->environment;
    }

    public function registerBundles()
    {
        return $this->configuration->getBundles();
    }

    /**
     * Returns a loader for the container.
     *
     * @param ContainerInterface $container The service container
     *
     * @return DelegatingLoader The loader
     */
    protected function getContainerLoader(ContainerInterface $container)
    {
        foreach ($this->configuration->getParameters() as $key => $val) {
            $container->setParameter($key, $val);
        }

        $locator = new FileLocator($this);
        $resolver = new LoaderResolver(array(
            new ArrayLoader($container),
            new XmlFileLoader($container, $locator),
            new YamlFileLoader($container, $locator),
            new IniFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new ClosureLoader($container),
        ));

        return new DelegatingLoader($resolver);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configs = Finder::create()
            ->name('*.yml')
            ->in($this->getConfigDir());

        foreach ($configs as $file) {
            $this->loadConfigFile($file, basename($file, '.yml'), $loader);
        }

        if (file_exists($parameters = $this->getRootDir().'/parameters.yml')) {
            $this->loadConfigFile($parameters, null, $loader);
        }
    }

    protected function loadConfigFile($file, $name = null, LoaderInterface $loader)
    {
        $configs = Yaml::parse($file);
        $env     = $this->getEnvironment();

        if (isset($configs['all'])) {
            $config = $name ? array($name => $configs['all']) : $configs['all'];
            $loader->load($config, $file);
        }
        if (isset($configs[$env])) {
            $config = $name ? array($name => $configs[$env]) : $configs[$env];
            $loader->load($config, $file);
        }

        foreach ($this->configuration->getConfigs() as $config) {
            if (file_exists($config = $this->getRootDir().'/'.$config)) {
                $loader->load($config);
            }
        }
    }
}
