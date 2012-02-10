<?php

namespace Knp\Bundle\RadBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

/**
 * Generates a CRUD controller.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerGenerator extends Generator
{
    private $skeletonDir;
    private $bundle;

    /**
     * Constructor.
     *
     * @param string $skeletonDir Path to the skeleton directory
     */
    public function __construct($skeletonDir)
    {
        $this->skeletonDir = $skeletonDir;
    }

    /**
     * Generate the CRUD controller.
     *
     * @param BundleInterface $bundle A bundle object
     * @param string $entity The entity relative class name
     * @param ClassMetadataInfo $metadata The entity class metadata
     * @param string $routePrefix The route name prefix
     * @param array $needWriteActions Wether or not to generate write actions
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $controller)
    {
        $this->bundle = $bundle;

        $this->generateControllerClass($controller);

        $dir = sprintf('%s/Resources/views/%s', $this->bundle->getPath(), str_replace('\\', '/', $controller));
    }

    /**
     * Generates the controller class only.
     *
     */
    private function generateControllerClass($controller)
    {
        $dir = $this->bundle->getPath();

        $target = sprintf(
            '%s/Controller/%sController.php',
            $dir,
            $controller
        );

        if (file_exists($target)) {
            return;
        }

        $this->renderFile($this->skeletonDir, 'controller.php', $target, array(
            'dir'               => $this->skeletonDir,
            'controller'        => $controller,
            'bundle'            => $this->bundle->getName(),
            'namespace'         => $this->bundle->getNamespace(),
            'format'            => 'yml'
        ));
    }

    /**
     * Generates the functional test class only.
     *
     */
    private function generateTestClass()
    {
        $parts = explode('\\', $this->entity);
        $entityClass = array_pop($parts);
        $entityNamespace = implode('\\', $parts);

        $dir    = $this->bundle->getPath() .'/Tests/Controller';
        $target = $dir .'/'. str_replace('\\', '/', $entityNamespace).'/'. $entityClass .'ControllerTest.php';

        $this->renderFile($this->skeletonDir, 'tests/test.php', $target, array(
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'entity'            => $this->entity,
            'entity_class'      => $entityClass,
            'namespace'         => $this->bundle->getNamespace(),
            'actions'           => $this->actions,
            'dir'               => $this->skeletonDir,
        ));
    }
}
