<?php

namespace Knp\Bundle\RadBundle\HttpKernel;

use Symfony\Component\Yaml\Yaml;

/**
 * KernelConfiguration.
 */
class KernelConfiguration
{
    private $kernel;
    private $configsMTime;
    private $organizationName;
    private $applicationName;
    private $configs    = array();
    private $bundles    = array();
    private $parameters = array();

    public function __construct(RadKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function load()
    {
        if (file_exists($cfg = $this->kernel->getRootDir().'/app.yml')) {
            $this->updateFromFile($cfg, $this->kernel->getEnvironment());
        }
        if (file_exists($cfg = $this->kernel->getRootDir().'/app.local.yml')) {
            $this->updateFromFile($cfg, $this->kernel->getEnvironment());
        }
    }

    public function updateFromFile($path, $environment)
    {
        $cacheFile = $this->kernel->getCacheDir().'/'.basename($path).'.cache';

        if (!file_exists($cacheFile) || filemtime($path) > filemtime($cacheFile)) {
            $parsed = Yaml::parse($path);

            if (!is_dir($cacheDir = dirname($cacheFile))) {
                mkdir($cacheDir, 0777, true);
            }
            file_put_contents($cacheFile, sprintf('<?php return %s;', var_export($parsed, true)));
        }

        $this->configsMTime = filemtime($cacheFile);
        $config = require($cacheFile);

        if (isset($config['organization'])) {
            $this->organizationName = $config['organization'];
        }
        if (isset($config['application'])) {
            $this->applicationName = $config['application'];
        }

        if (isset($config['all'])) {
            $this->loadSettings($config['all']);
        }
        if (isset($config[$environment])) {
            $this->loadSettings($config[$environment]);
        }
    }

    public function getOrganizationName()
    {
        if (null === $this->organizationName) {
            throw new \RuntimeException(
                'Specify your `organization` name inside app/app.yml or app/app.local.yml'
            );
        }

        return $this->organizationName;
    }

    public function getApplicationName()
    {
        if (null === $this->applicationName) {
            throw new \RuntimeException(
                'Specify your `application` name inside app/app.yml or app/app.local.yml'
            );
        }

        return $this->applicationName;
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    public function getBundles()
    {
        $cacheFile = $this->kernel->getCacheDir().'/bundles.php.cache';

        if (!file_exists($cacheFile) || $this->configsMTime > filemtime($cacheFile)) {
            $bundles  = "<?php return array(\n";

            foreach ($this->bundles as $class => $arguments) {
                $arguments = array_map(function($argument) {
                    if ('@' === substr($argument, 0, 1)) {
                        return '$'.substr($argument, 1);
                    }
                    if (is_numeric($argument)) {
                        return $argument;
                    }

                    return '"'.$argument.'"';
                }, (array) $arguments);

                $bundles .= sprintf("    new %s(%s),\n", $class, implode(', ', $arguments));
            }

            $bundles .= ");";

            if (!is_dir($cacheDir = dirname($cacheFile))) {
                mkdir($cacheDir, 0777, true);
            }
            file_put_contents($cacheFile, $bundles);
        }

        $kernel = $this->kernel;

        return require($cacheFile);
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    private function loadSettings(array $settings)
    {
        if (isset($settings['bundles'])) {
            foreach ($settings['bundles'] as $class => $constr) {
                $this->bundles[$class] = $constr;
            }
        }

        if (isset($settings['parameters'])) {
            foreach ($settings['parameters'] as $key => $val) {
                $this->parameters[$key] = $val;
            }
        }

        if (isset($settings['configs'])) {
            foreach ($settings['configs'] as $config) {
                $this->configs[] = $config;
            }
        }
    }
}
