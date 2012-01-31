<?php

namespace Knp\Bundle\RadBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser as BaseNameParser;

use Knp\Bundle\RadBundle\Bundle\ConventionalBundle;
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
        } elseif (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $template = parent::parse($name);
        $name     = str_replace(':/', ':', preg_replace('#/{2,}#', '/', strtr($name, '\\', '/')));

        if ($template->get('bundle')) {
            if ($this->kernel->getBundle($template->get('bundle')) instanceof ConventionalBundle) {
                return $this->cache[$name] = new TemplateReference(
                    $template->get('bundle'),
                    $template->get('controller'),
                    $template->get('name'),
                    $template->get('format'),
                    $template->get('engine')
                );
            }
        } else {
            return $this->cache[$name] = new TemplateReference(
                $template->get('bundle'),
                $template->get('controller'),
                $template->get('name'),
                $template->get('format'),
                $template->get('engine')
            );
        }

        return $template;
    }
}
