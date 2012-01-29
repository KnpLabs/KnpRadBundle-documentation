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
    private $projectName;
    private $configs    = array();
    private $apps       = array();
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

            if (!is_dir($this->kernel->getCacheDir())) {
                mkdir($this->kernel->getCacheDir(), 0777, true);
            }
            file_put_contents($cacheFile, sprintf('<?php return %s;', var_export($parsed, true)));
        }

        $this->configsMTime = filemtime($cacheFile);
        $config = require($cacheFile);

        if (isset($config['project'])) {
            $this->projectName = $config['project'];
        }

        if (isset($config['all'])) {
            $this->loadSettings($config['all']);
        }
        if (isset($config[$environment])) {
            $this->loadSettings($config[$environment]);
        }
    }

    public function getProjectName()
    {
        if (null === $this->projectName) {
            throw new \RuntimeException(
                'Specify your `project` name inside app/app.yml or app/app.local.yml'
            );
        }

        return $this->projectName;
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

                $bundles .= sprintf(
                    "    new %s(%s),\n", $class, implode(', ', $arguments)
                );
            }

            foreach ($this->apps as $name) {
                $bundles .= sprintf(
                    "    new Knp\Bundle\RadBundle\Bundle\ApplicationBundle(\$kernel, '%s'),\n",
                    $name
                );
            }

            $bundles .= ");";

            if (!is_dir($this->kernel->getCacheDir())) {
                mkdir($this->kernel->getCacheDir(), 0777, true);
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
        if (isset($settings['apps'])) {
            foreach ($settings['apps'] as $name) {
                if (!in_array($name, $this->apps)) {
                    $this->apps[] = $name;
                }
            }
        }

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
