<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\Routing\Loader;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;

/**
 * Automatically loads routes from application bundle
 */
class ApplicationBundlesLoader extends YamlFileLoader
{
    private $kernel;

    /**
     * Initializes routing loader.
     *
     * @param KernelInterface      $kernel  Kernel instance
     * @param FileLocatorInterface $locator File locator
     */
    public function __construct(KernelInterface $kernel, FileLocatorInterface $locator)
    {
        parent::__construct($locator);

        $this->kernel = $kernel;
    }

    /**
     * Loads a all ApplicationBundles routes.
     *
     * @param string $file The anything
     * @param string $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function load($file, $type = null)
    {
        $collection = new RouteCollection();

        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof ApplicationBundle) {
                if (file_exists($routing = $bundle->getPath().'/config/routing.yml')) {
                    $collection->addCollection(parent::load($routing));
                    $collection->addResource(new FileResource($routing));
                }

                if (is_dir($dir = $bundle->getPath().'/config/routing')) {
                    foreach (glob($dir.'/*.yml') as $routing) {
                        $collection->addCollection(parent::load($routing));
                        $collection->addResource(new FileResource($routing));
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean True if this class supports the given resource, false otherwise
     *
     * @api
     */
    public function supports($resource, $type = null)
    {
        return 'rad' === $type;
    }
}
