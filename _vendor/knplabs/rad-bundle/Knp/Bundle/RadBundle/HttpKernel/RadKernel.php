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
    private $organizationName;

    public function setOrganizationName($organizationName)
    {
        $this->organizationName = $organizationName;
    }

    public function getOrganizationName()
    {
        if (null === $this->organizationName) {
            if (!file_exists($config = $this->getRootDir().'/parameters.yml')) {
                throw new \RuntimeException(
                    'Specify your `organization` name inside app/parameters.yml '.
                    'or by directly calling RadKernel::setOrganizationName().'
                );
            }

            $config = Yaml::parse($config);

            if (!isset($config['organization'])) {
                throw new \RuntimeException(
                    'Specify your `organization` name at the root of app/parameters.yml.'
                );
            }

            $this->organizationName = $config['organization'];
        }

        return $this->organizationName;
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
        $kernel = $this;

        return require($this->getRootDir().'/bundles.php');
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
    }
}
