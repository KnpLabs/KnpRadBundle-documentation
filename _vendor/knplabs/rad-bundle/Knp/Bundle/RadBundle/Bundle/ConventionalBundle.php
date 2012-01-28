<?php

namespace Knp\Bundle\RadBundle\Bundle;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

use Knp\Bundle\RadBundle\Extension\ConventionalExtension;
use Knp\Bundle\RadBundle\HttpKernel\RadKernel;

class ConventionalBundle extends ContainerAware implements BundleInterface
{
    protected $name;
    protected $kernel;
    protected $parent;
    protected $namespace;
    protected $path;
    protected $extension;

    public function __construct(RadKernel $kernel, $name, $parent = null)
    {
        $this->kernel = $kernel;
        $this->name   = $name;
        $this->parent = $parent;
    }

    /**
     * Boots the Bundle.
     */
    public function boot()
    {
    }

    /**
     * Shutdowns the Bundle.
     */
    public function shutdown()
    {
    }

    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container A ContainerBuilder instance
     */
    public function build(ContainerBuilder $container)
    {
    }

    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $basename = preg_replace('/Bundle$/', '', $this->getName());

            $class = $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
            if (class_exists($class)) {
                $extension = new $class();

                // check naming convention
                $expectedAlias = Container::underscore($basename);
                if ($expectedAlias != $extension->getAlias()) {
                    throw new \LogicException(sprintf(
                        'The extension alias for the default extension of a '.
                        'bundle must be the underscored version of the '.
                        'bundle name ("%s" instead of "%s")',
                        $expectedAlias, $extension->getAlias()
                    ));
                }

                $this->extension = $extension;
            } else {
                $this->extension = new ConventionalExtension($this->getPath());
            }
        }

        if ($this->extension) {
            return $this->extension;
        }
    }

    /**
     * Gets the Bundle namespace.
     *
     * @return string The Bundle namespace
     */
    public function getNamespace()
    {
        if (null === $this->namespace) {
            $this->namespace = sprintf('%s\\Bundle\\%s',
                $this->kernel->getConfiguration()->getOrganizationName(),
                $this->getName()
            );
        }

        return $this->namespace;
    }

    /**
     * Gets the Bundle directory path.
     *
     * @return string The Bundle absolute path
     */
    public function getPath()
    {
        if (null === $this->path) {
            $this->path = sprintf('%s/src/%s/Bundle/%s',
                $this->kernel->getProjectDir(),
                $this->kernel->getConfiguration()->getOrganizationName(),
                $this->getName()
            );
        }

        return $this->path;
    }

    /**
     * Returns the bundle parent name.
     *
     * @return string The Bundle parent name it overrides or null if no parent
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Returns the bundle name (the class short name).
     *
     * @return string The Bundle name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Finds and registers Commands.
     *
     * Override this method if your bundle commands do not follow the conventions:
     *
     * * Commands are in the 'Command' sub-directory
     * * Commands extend Symfony\Component\Console\Command\Command
     *
     * @param Application $application An Application instance
     */
    public function registerCommands(Application $application)
    {
        if (!$dir = realpath($this->getPath().'/Command')) {
            return;
        }

        $finder = new Finder();
        $finder->files()->name('*Command.php')->in($dir);

        $prefix = $this->getNamespace().'\\Command';
        foreach ($finder as $file) {
            $ns = $prefix;
            if ($relativePath = $file->getRelativePath()) {
                $ns .= '\\'.strtr($relativePath, '/', '\\');
            }
            $r = new \ReflectionClass($ns.'\\'.$file->getBasename('.php'));
            if ($r->isSubclassOf('Symfony\\Component\\Console\\Command\\Command') && !$r->isAbstract()) {
                $application->add($r->newInstance());
            }
        }
    }
}
