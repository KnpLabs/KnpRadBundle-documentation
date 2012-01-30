<?php

namespace Knp\Bundle\RadBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class TranslationsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('translator')) {
            return;
        }

        $translator = $container->findDefinition('translator');
        $projectDir = $container->getParameter('kernel.project_dir');

        $dirs = array();
        foreach ($container->getParameter('kernel.bundles') as $name => $class) {
            if (is_dir($dir = dirname($projectDir.'/'.str_replace('\\', '/', $name)).'/i18n')) {
                $dirs[] = $dir;
            }
        }

        if (is_dir($dir = $projectDir.'/i18n')) {
            $dirs[] = $dir;
        }

        // Register translation resources
        if ($dirs) {
            $finder = new Finder();
            $finder->files()->filter(function (\SplFileInfo $file) {
                return 2 === substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
            })->in($dirs);

            foreach ($finder as $file) {
                // filename is domain.locale.format
                list($domain, $locale, $format) = explode('.', $file->getBasename(), 3);

                $translator->addMethodCall('addResource', array($format, (string) $file, $locale, $domain));
            }
        }
    }
}
