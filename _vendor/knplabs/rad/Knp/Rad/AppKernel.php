<?php

namespace Knp\Rad;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Yaml\Yaml;

use Knp\Rad\DependencyInjection\Loader\ArrayLoader;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\IniFileLoader;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Finder\Finder;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new \Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new \Test\BlogBundle\TestBlogBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new \Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new \Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return realpath(__DIR__.'/../../../../../app');
    }

    public function getLogDir()
    {
        return realpath($this->rootDir.'/../waste/logs');
    }

    public function getCacheDir()
    {
        return realpath($this->rootDir.'/../waste/cache/'.$this->environment);
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
        $dir = $this->rootDir.'/config';
        $env = $this->getEnvironment();

        if (file_exists($dir.'/parameters.ini')) {
            $loader->load($dir.'/parameters.ini');
        }

        foreach (Finder::create()->files()->name('/^(?!routing).*\.yml/')->in($dir) as $file) {
            $name    = basename($file, '.yml');
            $configs = Yaml::parse($file);

            if (isset($configs['prod'])) {
                $loader->load(array($name => $configs['prod']));
            }
            if (in_array($env, array('test', 'dev')) && isset($configs['dev'])) {
                $loader->load(array($name => $configs['dev']));
            }
            if ('test' === $env && isset($configs['test'])) {
                $loader->load(array($name => $configs['test']));
            }
        }

        if (file_exists($dir.'/config.yml')) {
            $loader->load($dir.'/config.yml');
        }
    }
}
