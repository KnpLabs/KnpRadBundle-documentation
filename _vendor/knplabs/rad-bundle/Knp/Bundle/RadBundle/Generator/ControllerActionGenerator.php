<?php

namespace Knp\Bundle\RadBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Knp\Bundle\RadBundle\Manipulator\ControllerManipulator;

/**
 * Generates a controller action.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ControllerActionGenerator extends Generator
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
     * Generate the controller action.
     *
     * @param BundleInterface $bundle A bundle object
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $controller, $action)
    {
        $this->bundle   = $bundle;

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
            'format'            => 'yml'
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
