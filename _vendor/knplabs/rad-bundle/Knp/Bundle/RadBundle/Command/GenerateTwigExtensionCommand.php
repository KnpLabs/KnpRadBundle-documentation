<?php

namespace Knp\Bundle\RadBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Knp\Bundle\RadBundle\Generator\TwigExtensionGenerator;

class GenerateTwigExtensionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('rad:generate:twig-extension')
            ->setDescription('Generates a Twig extension with its service definition.')
            ->addArgument('name', InputArgument::REQUIRED, $description = 'The extension name')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new TwigExtensionGenerator($this->getApplication()->getKernel());
        $generator->generate(
            'App',  // @todo is it always the wanted bundle?
            $input->getArgument('name')
        );
    }
}
