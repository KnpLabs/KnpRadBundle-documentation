<?php

namespace Knp\Bundle\RadBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Sensio\Bundle\GeneratorBundle\Manipulator\KernelManipulator;
use Sensio\Bundle\GeneratorBundle\Manipulator\RoutingManipulator;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Knp\Bundle\RadBundle\Generator\ControllerGenerator;
use Knp\Bundle\RadBundle\Generator\ControllerActionGenerator;

/**
 * Generates controllers.
 */
class GenerateControllerCommand extends ContainerAwareCommand
{
    private $bundleGenerator;
    private $controllerGenerator;
    private $controllerActionGenerator;

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDefinition(array(
                new InputArgument('name', InputArgument::REQUIRED, 'The name'),
            ))
            ->setDescription('Generates a bundle:controller:action')
            ->setName('rad:generate:controller')
            ->setHelp(<<<EOT
The <info>rad:generate</info> command create skeletons of code based on snippets.

<info>php app/console rad:generate AcmeHello</info>

A "AcmeHello" bundle will be created inside the target directory, if not exist.


<info>php app/console rad:generate AcmeHello:Default</info>

A "DefaultController" class will be created, if not exist.
A "AcmeHello" bundle will be created inside the target directory, if not exist.


<info>php app/console rad:generate AcmeHello:Default:index</info>

A index method will be create in the controller, with corresponding template file.
A "DefaultController" class will be created, if not exist.
A "AcmeHello" bundle will be created inside the target directory, if not exist.

EOT
            )
        ;
    }

    /**
     * @see Command
     *
     * @throws \InvalidArgumentException When namespace doesn't end with Bundle
     * @throws \RuntimeException         When bundle can't be executed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $names = explode(':', $input->getArgument('name'));

        $this->dir       = $this->getContainer()->getParameter('kernel.project_dir');
        $this->namespace = $this->getContainer()->getParameter('kernel.project_name');
        $this->patterns  = array('view' => '%s/views/%s');

        $this->handle($names);
    }

    public function handle(array $names)
    {
        $bundle = 'App';
        @list($controller, $action) = $names;

        switch (count($names)) {
            case 1:
                $this->generateBundle($bundle);
                $this->generateController($bundle, $controller);
                break;
            case 2:
                $this->generateBundle($bundle);
                $this->generateController($bundle, $controller);
                $this->generateControllerAction($bundle, $controller, $action);
                break;
            default:
                break;
        }
    }

    public function generateBundle($bundle)
    {
        try {
            $this->getApplication()->getKernel()->getBundle($bundle);
            return;
        }
        catch(\Exception $e) {
        }
        $dir = Validators::validateTargetDir($this->dir, $bundle, $this->namespace);
        $format = 'yml';
        $structure = true;

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generator = $this->getBundleGenerator();
        $generator->generate($this->namespace, $bundle, $dir, $format, $structure);
    }

    public function generateController($bundle, $controller)
    {
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getControllerGenerator();
        $generator->generate($bundle, $controller);
    }

    public function generateControllerAction($bundle, $controller, $action)
    {
        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getControllerActionGenerator($this->patterns);
        $generator->generate($bundle, $controller, $action);
    }

    protected function getBundleGenerator()
    {
        if (null === $this->bundleGenerator) {
            $this->bundleGenerator = new BundleGenerator(
                $this->getContainer()->get('filesystem'),
                $this->getSkeletonLocation('@KnpRadBundle/Resources/skeleton/bundle')
            );
        }

        return $this->bundleGenerator;
    }

    public function setBundleGenerator(BundleGenerator $generator)
    {
        $this->bundleGenerator = $generator;
    }

    protected function getControllerGenerator()
    {
        if (null === $this->controllerGenerator) {
            $this->controllerGenerator = new ControllerGenerator(
                $this->getSkeletonLocation('@KnpRadBundle/Resources/skeleton/controller')
            );
        }

        return $this->controllerGenerator;
    }

    public function setControllerGenerator(ControllerGenerator $generator)
    {
        $this->controllerGenerator = $generator;
    }

    protected function getControllerActionGenerator(array $patterns)
    {
        if (null === $this->controllerActionGenerator) {
            $this->controllerActionGenerator = new ControllerActionGenerator(
                $this->getSkeletonLocation('@KnpRadBundle/Resources/skeleton'),
                $patterns
            );
        }

        return $this->controllerActionGenerator;
    }

    public function setControllerActionGenerator(ControllerActionGenerator $generator)
    {
        $this->controllerActionGenerator = $generator;
    }

    private function getSkeletonLocation($dir)
    {
        return $this->getContainer()->get('kernel')->locateResource($dir);
    }
}
