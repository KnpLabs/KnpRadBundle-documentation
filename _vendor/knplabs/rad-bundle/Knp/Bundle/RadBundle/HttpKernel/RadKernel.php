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
use Doctrine\Common\Annotations\AnnotationRegistry;

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

    static public function autoload($loader)
    {
        if (file_exists($custom = self::getProjectDir().'/app/autoload.php')) {
            return require($custom);
        }

        // intl
        if (!function_exists('intl_get_error_code')) {
            require_once self::getProjectDir().'/vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
            $loader->add(null, self::getProjectDir().'/vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs');
        }

        // Swiftmailer needs a special autoloader to allow
        // the lazy loading of the init file (which is expensive)
        require_once self::getProjectDir().'/vendor/swiftmailer/swiftmailer/lib/classes/Swift.php';
        \Swift::registerAutoload(self::getProjectDir().'/vendor/swiftmailer/swiftmailer/lib/swift_init.php');
    }

    static public function getProjectDir()
    {
        return realpath(__DIR__.'/../../../../../../..');
    }

    public function getRootDir()
    {
        return self::getProjectDir().'/app';
    }

    public function getConfigDir()
    {
        return $this->getRootDir().'/config';
    }

    public function getLogDir()
    {
        return self::getProjectDir().'/logs';
    }

    public function getCacheDir()
    {
        return self::getProjectDir().'/cache/'.$this->environment;
    }

    public function getConfiguration()
    {
        return $this->configuration;
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
