<?php

namespace Knp\Bundle\RadBundle\HttpKernel;

use Symfony\Component\Yaml\Yaml;

/**
 * KernelConfiguration.
 */
class KernelConfiguration
{
    private $configDir;
    private $cacheDir;
    private $configsMTime;
    private $projectName;
    private $applicationName;
    private $configs    = array();
    private $bundles    = array();
    private $parameters = array();

    public function __construct($configDir, $cacheDir)
    {
        $this->configDir = $configDir;
        $this->cacheDir  = $cacheDir;
    }

    public function load($environment)
    {
        if (file_exists($cfg = $this->configDir.'/project.yml')) {
            $this->updateFromFile($cfg, $environment);
        }
        if (file_exists($cfg = $this->configDir.'/project.local.yml')) {
            $this->updateFromFile($cfg, $environment);
        }
    }

    public function updateFromFile($path, $environment)
    {
        $cacheFile = $this->cacheDir.'/'.basename($path).'.cache';

        if (!file_exists($cacheFile) || filemtime($path) > filemtime($cacheFile)) {
            $parsed = Yaml::parse($path);

            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
            }
            file_put_contents($cacheFile, sprintf('<?php return %s;', var_export($parsed, true)));
        }

        $this->configsMTime = filemtime($cacheFile);
        $config = require($cacheFile);

        if (isset($config['name'])) {
            $this->projectName     = $config['name'];
            $this->applicationName = preg_replace('/(?:.*\\\)?([^\\\]+)$/', '$1', $config['name']);
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
                'Specify your project `name` inside app/project.yml or app/project.local.yml'
            );
        }

        return $this->projectName;
    }

    public function getApplicationName()
    {
        if (null === $this->applicationName) {
            throw new \RuntimeException(
                'Specify your project `name` inside app/project.yml or app/project.local.yml'
            );
        }

        return $this->applicationName;
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    public function getBundles(RadKernel $kernel)
    {
        $cacheFile = $this->cacheDir.'/bundles.php.cache';

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

            $bundles .= sprintf(
                "    new Knp\Bundle\RadBundle\Bundle\ApplicationBundle('%s', '%s'),\n",
                $this->getProjectName(),
                $kernel->getRootDir().'/src'
            );

            $bundles .= ");";

            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0777, true);
            }
            file_put_contents($cacheFile, $bundles);
        }

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
