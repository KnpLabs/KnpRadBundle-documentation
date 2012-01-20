<?php

namespace Knp\Bundle\SoRBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Knp\Bundle\SoRBundle\Manipulator\ControllerManipulator;

/**
 * Generates a controller action.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerActionGenerator extends Generator
{
    private $filesystem;
    private $skeletonDir;
    private $bundle;
    private $format;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     * @param string $skeletonDir Path to the skeleton directory
     */
    public function __construct(Filesystem $filesystem, $skeletonDir)
    {
        $this->filesystem  = $filesystem;
        $this->skeletonDir = $skeletonDir;
    }

    /**
     * Generate the controller action.
     *
     * @param BundleInterface $bundle A bundle object
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $controller, $action)
    {
        $this->bundle   = $bundle;
        $this->setFormat('yml');

        if (!$this->actionExists($controller, $action)) {
            $this->generateControllerAction($controller, $action);
        }

        $dir = sprintf('%s/Resources/views/%s', $this->bundle->getPath(), str_replace('\\', '/', $controller));

        $this->generateView($dir, $action);
    }

    private function actionExists($controller, $action)
    {
        $controllerNs = sprintf('%s\\Controller\\%sController', $this->bundle->getNamespace(), str_replace('\\', '/', $controller));
        $r = new \ReflectionClass($controllerNs);

        return $r->hasMethod($action);
    }

    /**
     * Sets the configuration format.
     *
     * @param string $format The configuration format
     */
    private function setFormat($format)
    {
        switch ($format) {
            case 'yml':
            case 'xml':
            case 'php':
            case 'annotation':
                $this->format = $format;
                break;
            default:
                $this->format = 'yml';
                break;
        }
    }

    /**
     * Generates the routing configuration.
     *
     */
    private function generateConfiguration()
    {
        if (!in_array($this->format, array('yml', 'xml', 'php'))) {
            return;
        }

        $target = sprintf(
            '%s/Resources/config/routing/%s.%s',
            $this->bundle->getPath(),
            strtolower(str_replace('\\', '_', $this->entity)),
            $this->format
        );

        $this->renderFile($this->skeletonDir, 'config/routing.'.$this->format, $target, array(
            'actions'           => $this->actions,
            'route_prefix'      => $this->routePrefix,
            'route_name_prefix' => $this->routeNamePrefix,
            'bundle'            => $this->bundle->getName(),
            'entity'            => $this->entity,
        ));
    }

    /**
     * Generates the controller class only.
     *
     */
    private function generateControllerAction($controller, $action)
    {
        $dir = $this->bundle->getPath();

        $code = $this->render($this->skeletonDir, 'controller/action.php', array(
            'action'            => $action,
            'dir'               => $this->skeletonDir,
            'bundle'            => $this->bundle->getName(),
            'namespace'         => $this->bundle->getNamespace(),
            'format'            => $this->format,
        ));

        $manipulator = new ControllerManipulator(sprintf('%s\\Controller\\%sController', $this->bundle->getNamespace(), $controller));
        $manipulator->addAction($code);
    }

    /**
     * Generates the index.html.twig template in the final bundle.
     *
     * @param string $dir The path to the folder that hosts templates in the bundle
     */
    private function generateView($dir, $action)
    {
        $this->renderFile($this->skeletonDir, 'views/action.html.twig', sprintf('%s/%s.html.twig', $dir, $action), array(
        ));
    }
}
