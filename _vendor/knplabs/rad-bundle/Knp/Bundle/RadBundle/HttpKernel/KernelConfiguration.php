<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\HttpKernel;

use Symfony\Component\Yaml\Yaml;

/**
 * RAD kernel configuration class.
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

    /**
     * Initializes configuration.
     *
     * @param string $configDir Directory to confgs
     * @param string $cacheDir  Directory to cache
     */
    public function __construct($configDir, $cacheDir)
    {
        $this->configDir = $configDir;
        $this->cacheDir  = $cacheDir;
    }

    /**
     * Loads conventional configs for specific configuration.
     *
     * @param string $environment Environment name
     */
    public function load($environment)
    {
        if (file_exists($cfg = $this->configDir.'/project.yml')) {
            $this->updateFromFile($cfg, $environment);
        }
        if (file_exists($cfg = $this->configDir.'/project.local.yml')) {
            $this->updateFromFile($cfg, $environment);
        }
    }

    /**
     * Updates configuration from specified configuration file.
     *
     * @param string $path        Configuration file path
     * @param string $environment Environment name
     */
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
        } else {
            throw new \InvalidArgumentException(
                'Specify your project `name` inside config/project.yml or config/project.local.yml'
            );
        }

        if (isset($config['all'])) {
            $this->loadSettings($config['all']);
        }
        if (isset($config[$environment])) {
            $this->loadSettings($config[$environment]);
        }
    }

    /**
     * Returns full project name (application namespace).
     *
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * Returns application name (last segment of applicatoin namespace).
     *
     * @return string
     */
    public function getApplicationName()
    {
        return $this->applicationName;
    }

    /**
     * Returns array of custom config DIC files paths.
     *
     * @return string
     */
    public function getConfigs()
    {
        return $this->configs;
    }

    /**
     * Returns array of initialized bundles.
     *
     * @param RadKernel $kernel RadAppKernel instance
     *
     * @return array
     */
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

                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Bundle class "%s" does not exists or can not be found.',
                        $class
                    ));
                }

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

    /**
     * Returns configuration parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Loads specific settings from array.
     *
     * @param array $settings Settings array
     */
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
