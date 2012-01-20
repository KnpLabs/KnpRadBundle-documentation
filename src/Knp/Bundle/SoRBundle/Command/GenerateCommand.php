<?php

namespace Knp\Bundle\SoRBundle\Command;

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
use Knp\Bundle\SoRBundle\Generator\ControllerGenerator;
use Knp\Bundle\SoRBundle\Generator\ControllerActionGenerator;

/**
 * Generates bundles.
 *
 */
class GenerateCommand extends ContainerAwareCommand
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
                new InputArgument('names', InputArgument::REQUIRED, 'The names'),
                new InputOption('vendor', '', InputOption::VALUE_REQUIRED, 'The vendor of the project', 'Knp'),
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to create', 'Knp'),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the bundle', 'src'),
            ))
            ->setDescription('Generates a bundle:controller:action')
            ->setName('generate')
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
        $names = $input->getArgument('names');
        $this->dir = $input->getOption('dir');
        $this->namespace = $input->getOption('namespace');

        $names = explode(':', $names);

        $this->handle($names);
    }

    public function handle(array $names)
    {
        $bundle = $names[0].'Bundle';
        switch (count($names)) {
            case 1:
                $this->generateBundle($bundle);
                break;
            case 2:
                $this->generateBundle($bundle);
                $this->generateController($bundle, $names[1]);
                break;
            case 3:
                $this->generateBundle($bundle);
                $this->generateController($bundle, $names[1]);
                $this->generateControllerAction($bundle, $names[1], $names[2]);
                break;
            default:
                break;
        }
    }

    public function generateBundle($bundle)
    {
        $bundle = Validators::validateBundleName($bundle);
        try {
            $this->getApplication()->getKernel()->getBundle($bundle);
            return;
        }
        catch(\Exception $e) {
        }
        $dir = Validators::validateTargetDir($this->dir, $bundle, 'Knp');
        $format = Validators::validateFormat('yml');
        $structure = true;

        if (!$this->getContainer()->get('filesystem')->isAbsolutePath($dir)) {
            $dir = getcwd().'/'.$dir;
        }

        $generator = $this->getBundleGenerator();
        $generator->generate($this->namespace, $bundle, $dir, $format, $structure);
    }

    public function generateController($bundle, $controller)
    {
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getControllerGenerator();
        $generator->generate($bundle, $controller);
    }

    public function generateControllerAction($bundle, $controller, $action)
    {
        $bundle      = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getControllerActionGenerator();
        $generator->generate($bundle, $controller, $action);
    }



    protected function getBundleGenerator()
    {
        if (null === $this->bundleGenerator) {
            $this->bundleGenerator = new BundleGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/bundle');
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
            $this->controllerGenerator = new ControllerGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton/controller');
        }

        return $this->controllerGenerator;
    }

    public function setControllerGenerator(ControllerGenerator $generator)
    {
        $this->controllerGenerator = $generator;
    }

    protected function getControllerActionGenerator()
    {
        if (null === $this->controllerActionGenerator) {
            $this->controllerActionGenerator = new ControllerActionGenerator($this->getContainer()->get('filesystem'), __DIR__.'/../Resources/skeleton');
        }

        return $this->controllerActionGenerator;
    }

    public function setControllerActionGenerator(ControllerActionGenerator $generator)
    {
        $this->controllerActionGenerator = $generator;
    }
}
