<?php

namespace Knp\Bundle\RadBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateNameParser as BaseNameParser;

use Knp\Bundle\RadBundle\Bundle\ConventionalBundle;

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
        $template = parent::parse($name);

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
        }

        return $template;
    }
}
