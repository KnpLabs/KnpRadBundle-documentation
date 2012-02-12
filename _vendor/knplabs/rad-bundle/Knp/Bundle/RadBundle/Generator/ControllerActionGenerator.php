<?php

namespace Knp\Bundle\RadBundle\Generator;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Knp\Bundle\RadBundle\Manipulator\ControllerManipulator;

/**
 * Generates a controller action.
 */
class ControllerActionGenerator extends Generator
{
    private $skeletonDir;
    private $bundle;
    private $patterns;

    /**
     * Constructor.
     *
     * @param string $skeletonDir Path to the skeleton directory
     */
    public function __construct($skeletonDir, array $patterns)
    {
        $this->skeletonDir = $skeletonDir;

        $this->patterns = array_merge(array(
            'view' => '%s/Resources/views/%s',
            'namespace' => '%s\\Controller\\%sController',
        ), $patterns);
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
        $this->bundle = $bundle;

        if ($this->generateControllerAction($controller, $action)) {
            $dir = sprintf($this->patterns['view'], $this->bundle->getPath(), str_replace('\\', '/', $controller));
            $this->generateView($dir, $action);
        }
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

        $manipulator = new ControllerManipulator(sprintf($this->patterns['namespace'], $this->bundle->getNamespace(), $controller));
        return $manipulator->addAction($action, $code);
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

    //TODO: remove later when https://github.com/sensio/SensioGeneratorBundle/pull/112 be merged
    private function render($skeletonDir, $template, $parameters)
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($skeletonDir), array(
            'debug'            => true,
            'cache'            => false,
            'strict_variables' => true,
            'autoescape'       => false,
        ));

        return $twig->render($template, $parameters);
    }
}
