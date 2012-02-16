<?php

namespace Knp\Bundle\RadBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

class GenerateControllerCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('rad:generate:controller')
            ->setDescription('Generates a controller (or action) with its service routing.')
            ->addArgument('name', InputArgument::REQUIRED, 'The controller name name')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getApplication()->getKernel();
        $bundle = $kernel->getBundle('App');
        $name   = $input->getArgument('name');
        $twig   = $this->createTwig();
        $dialog = $this->getHelperSet()->get('dialog');

        $controller = $name;
        $action     = null;
        if (2 === count($parts = explode(':', $name))) {
            list($controller, $action) = $parts;
        }

        $class     = sprintf('%sController', str_replace('/', '\\', $controller));
        $subns     = dirname(str_replace('\\', '/', $class));
        $namespace = sprintf('%s\Controller%s', $bundle->getNamespace(), (
            '.' !== $subns ? '\\'.$subns : ''
        ));
        $classPath = sprintf('%s/Controller/%s.php',
            $bundle->getPath(), str_replace('\\', '/', $class)
        );
        $class     = basename(str_replace('\\', '/', $class));
        $fqcn      = sprintf('%s\%s', $namespace, $class);
        $name      = basename(str_replace('\\', '/', $name));

        if (!class_exists($fqcn)) {
            // generate the class
            $classData = $twig->render('controller.php.twig', array(
                'namespace' => $namespace,
                'class'     => $class,
                'name'      => $name
            ));
            $this->writeFile($classPath, $classData);

            $output->writeLn(sprintf(
                'Controller class <info>%s</info> generated in <info>%s</info>.',
                $fqcn,
                $classPath
            ));
        } else {
            $output->writeLn(sprintf(
                'The controller class <info>%s</info> already exists.',
                $fqcn
            ));
        }

        if (null === $action) {
            return;
        }

        $refl = new \ReflectionClass($fqcn);
        if (!$refl->hasMethod($action)) {
            $prefix = '';
            $lineToAppend = $refl->getEndLine();
            foreach ($refl->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($refl->getName() !== $method->getDeclaringClass()->getName()) {
                    continue;
                }

                $prefix = "\n";
                $lineToAppend = $method->getEndLine() + 1;
            }

            // generate the action
            $actionData = $prefix.$twig->render('action.php.twig', array(
                'action' => $action
            ));
            $this->appendToFile($refl->getFileName(), $lineToAppend - 2, $actionData);

            $output->writeLn(sprintf(
                'Action method <info>%s</info> generated in <info>%s</info> controller.',
                $action, $fqcn
            ));
        } else {
            $output->writeLn(sprintf(
                'The action <info>%s</info> already exists in <info>%s</info> controller.',
                $action, $fqcn
            ));
        }

        $viewPath = sprintf('%s/views/%s/%s.html.twig',
            $bundle->getPath(),
            str_replace('\\', '/', $controller),
            $action
        );

        if (!file_exists($viewPath)) {
            // generate the view
            $viewData = $twig->render('view.html.twig');
            $this->writeFile($viewPath, $viewData);

            $output->writeLn(sprintf('View <info>%s</info>.', $viewPath));
        } else {
            $output->writeLn(sprintf('The view <info>%s</info> already exists.', $viewPath));
        }
    }

    private function writeFile($path, $data, $flags = null)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $data, $flags);
    }

    private function appendToFile($path, $line, $data, $flags = null)
    {
        $content = file($path, $flags);
        $content = array_replace($content, array($line => $content[$line].$data));
        file_put_contents($path, implode('', $content), $flags);
    }

    private function createTwig()
    {
        $kernel    = $this->getApplication()->getKernel();
        $directory = $kernel->locateResource('@KnpRadBundle/Resources/skeleton/controller');

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
}
