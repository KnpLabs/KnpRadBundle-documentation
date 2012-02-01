<?php

namespace Knp\Bundle\RadBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser as BaseNameParser;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * TemplateNameParser.
 */
class TemplateNameParser extends BaseNameParser
{
    /**
     * {@inheritdoc}
     */
    public function parse($name)
    {
        if ($name instanceof TemplateReferenceInterface) {
            return $name;
        }

        $name = str_replace(':/', ':', preg_replace('#/{2,}#', '/', strtr($name, '\\', '/')));
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        if (preg_match('/^([^:]*):([^:]*)$/', $name, $matches)) {
            $elements = explode('.', $matches[2]);
            if (3 > count($elements)) {
                throw new \InvalidArgumentException(sprintf(
                    'Template name "%s" is not valid (format is "controller:template.format.engine").',
                    $name
                ));
            }
            $engine = array_pop($elements);
            $format = array_pop($elements);

            return $this->cache[$name] = new TemplateReference(
                $this->kernel->getConfiguration()->getApplicationName(),
                $matches[1],
                implode('.', $elements),
                $format,
                $engine
            );
        }

        return parent::parse($name);
    }
}
