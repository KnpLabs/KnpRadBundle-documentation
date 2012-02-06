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

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Yaml\Yaml;

use RadAppKernel;

/**
 * RAD kernel configuration class.
 */
class KernelConfiguration
{
    private $projectName;
    private $applicationName;
    private $imports    = array();
    private $parameters = array();
    private $bundles    = array();

    protected $environment;
    protected $configDir;
    protected $projectCache;
    protected $bundlesCache;

    /**
     * Initializes configuration.
     *
     * @param string  $environment Configuration environment
     * @param string  $configDir   Directory to confgs
     * @param string  $cacheDir    Directory to cache
     * @param Boolean $debug       Whether debugging in enabled or not
     */
    public function __construct($environment, $configDir, $cacheDir, $debug)
    {
        $this->environment  = $environment;
        $this->configDir    = $configDir;
        $this->projectCache = new ConfigCache($cacheDir.'/kernel.yml.cache', $debug);
        $this->bundlesCache = new ConfigCache($cacheDir.'/bundles.php.cache', $debug);
    }

    /**
     * Loads conventional configs for specific configuration.
     */
    public function load()
    {
        if (!$this->projectCache->isFresh()) {
            $metadata = array();

            if (file_exists($cfg = $this->configDir.'/kernel.yml')) {
                $this->updateFromFile($cfg, $this->environment);
                $metadata[] = new FileResource($cfg);
            }

            if (file_exists($cfg = $this->configDir.'/kernel.custom.yml')) {
                $this->updateFromFile($cfg, $this->environment);
                $metadata[] = new FileResource($cfg);
            }

            $this->projectCache->write('<?php return '.var_export(array(
                $this->projectName,
                $this->applicationName,
                $this->imports,
                $this->parameters,
                $this->bundles
            ), true).';', $metadata);
        }

        list(
            $this->projectName,
            $this->applicationName,
            $this->imports,
            $this->parameters,
            $this->bundles
        ) = require($this->projectCache);
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
     * Returns array of custom config DIC files paths.
     *
     * @return string
     */
    public function getImports()
    {
        return $this->imports;
    }

    /**
     * Returns array of initialized bundles.
     *
     * @param RadAppKernel $kernel Project kernel
     *
     * @return array
     */
    public function getBundles(RadAppKernel $kernel)
    {
        if (!$this->bundlesCache->isFresh()) {
            $this->bundlesCache->write(
                $this->generateBundlesCache($this->bundles),
                array(new FileResource((string) $this->projectCache))
            );
        }

        return require($this->bundlesCache);
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
     * Updates configuration from specified configuration file.
     *
     * @param string $path        Configuration file path
     * @param string $environment Environment name
     */
    private function updateFromFile($path, $environment)
    {
        $config = Yaml::parse($path);

        if (isset($config['name'])) {
            $this->projectName     = $config['name'];
            $this->applicationName = preg_replace('/(?:.*\\\)?([^\\\]+)$/', '$1', $config['name']);
        } else {
            throw new \InvalidArgumentException(
                'Specify your project `name` inside config/kernel.yml or config/kernel.local.yml'
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
     * Loads specific settings from array.
     *
     * @param array $settings Settings array
     */
    private function loadSettings(array $settings)
    {
        if (isset($settings['bundles'])) {
            foreach ($settings['bundles'] as $class) {
                $this->bundles[] = $class;
            }
        }

        if (isset($settings['parameters'])) {
            foreach ($settings['parameters'] as $key => $val) {
                $this->parameters[$key] = $val;
            }
        }

        if (isset($settings['imports'])) {
            foreach ($settings['imports'] as $config) {
                $this->imports[] = $config;
            }
        }
    }

    /**
     * Generates bundles cache string (*.php array file).
     *
     * @param array $bundles List of bundle classes
     *
     * @return string
     */
    private function generateBundlesCache(array $bundles)
    {
        $cache = "<?php return array(\n";

        foreach ($bundles as $class) {
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf(
                    'Bundle class "%s" does not exists or can not be found.',
                    $class
                ));
            }

            $cache .= sprintf("    new %s(\$kernel),\n", $class);
        }

        $cache .= ");";

        return $cache;
    }
}
