<?php

namespace Knp\Bundle\RadBundle\Routing\Loader;

use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Config\FileLocatorInterface;

use Knp\Bundle\RadBundle\Bundle\ConventionalBundle;

class ConventionalBundlesLoader extends YamlFileLoader
{
    private $kernel;

    public function __construct(KernelInterface $kernel, FileLocatorInterface $locator)
    {
        parent::__construct($locator);

        $this->kernel = $kernel;
    }

    /**
     * Loads a all ConventionalBundles routes.
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
            if ($bundle instanceof ConventionalBundle) {
                if (file_exists($routing = $bundle->getPath().'/config/routing.yml')) {
                    $collection->addCollection(parent::load($routing));
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
