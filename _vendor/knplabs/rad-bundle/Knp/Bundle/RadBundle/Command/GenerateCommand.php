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
                new InputArgument('name', InputArgument::REQUIRED, 'The name'),
                new InputOption('default', '', InputOption::VALUE_REQUIRED, 'The bundle to generate', 'ApplicationBundle'),
                new InputOption('vendor', '', InputOption::VALUE_REQUIRED, 'The vendor of the project', ''),
                new InputOption('namespace', '', InputOption::VALUE_REQUIRED, 'The namespace of the bundle to create', ''),
                new InputOption('dir', '', InputOption::VALUE_REQUIRED, 'The directory where to create the bundle', 'src'),
            ))
            ->setDescription('Generates a bundle:controller:action')
            ->setName('generate')
            ->setHelp(<<<EOT
The <info>generate</info> command create skeletons of code based on snippets.

<info>php app/console generate AcmeHello</info>

A "AcmeHello" bundle will be created inside the target directory, if not exist.


<info>php app/console generate AcmeHello:Default</info>

A "DefaultController" class will be created, if not exist.
A "AcmeHello" bundle will be created inside the target directory, if not exist.


<info>php app/console generate AcmeHello:Default:index</info>

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
        $names = $input->getArgument('name');
        $this->dir = $input->getOption('dir');
        $this->namespace = $input->getOption('namespace');
        $this->default = $input->getOption('default');

        $names = explode(':', $names);

        $this->handle($names);
    }

    public function handle(array $names)
    {
        list($bundle, $controller, $action) = $names;
        if (empty($bundle)) {
            $bundle = $this->default;
        }
        switch (count($names)) {
            case 1:
                $this->generateBundle($bundle);
                break;
            case 2:
                $this->generateBundle($bundle);
                $this->generateController($bundle, $controller);
                break;
            case 3:
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
