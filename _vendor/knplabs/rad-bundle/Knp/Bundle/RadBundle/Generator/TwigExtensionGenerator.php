<?php

namespace Knp\Bundle\RadBundle\Generator;

use Symfony\Component\HttpKernel\KernelInterface;

class TwigExtensionGenerator
{
    private $kernel;
    private $skeletonResource;

    public function __construct(KernelInterface $kernel, $skeletonResource = null)
    {
        $this->kernel           = $kernel;
        $this->skeletonResource = $skeletonResource ?: '@KnpRadBundle/Resources/skeleton/twig_extension';
    }

    /**
     * Generates a twig extension into the specified bundle
     *
     * @param  string $bundleName The name of the bundle you want to generate files in
     * @param  string $name       The name of the twig extension
     */
    public function generate($bundleName, $name)
    {
        $twig   = $this->createTwig();
        $bundle = $this->kernel->getBundle($bundleName);

        $class     = sprintf('%sExtension', $this->classify($name));
        $namespace = sprintf('%s\Twig', $bundle->getNamespace());
        $fqcn      = sprintf('%s\%s', $namespace, $class);

        // extension class rendering
        $classPath = sprintf('%s/Twig/%s.php', $bundle->getPath(), $class);
        $classData = $twig->render('extension.php.twig', array(
            'namespace' => $namespace,
            'class'     => $class,
            'name'      => $name
        ));

        $this->write($classPath, $classData);

        // service definition rendering
        $servicesPath = sprintf('%s/Resources/config/services.yml', $bundle->getPath());
        $servicesData = $twig->render('services.yml.twig', array(
            'name'      => $name,
            'fqcn'      => $fqcn,
            'bundle'    => $bundle->getName(),
        ));

        $this->write($servicesPath, $servicesData, FILE_APPEND);
    }

    private function write($path, $data, $flags = null)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $data, $flags);
    }

    private function createTwig()
    {
        $directory = $this->kernel->locateResource($this->skeletonResource);

        return new \Twig_Environment(
            new \Twig_Loader_Filesystem($directory),
            array(
                'debug'            => true,
                'cache'            => false,
                'strict_variables' => true,
                'autoescape'       => false,
            )
        );
    }

    private function classify($word)
    {
        return str_replace(' ', '', ucwords(strtr($word, '_-', '  ')));
    }
}
