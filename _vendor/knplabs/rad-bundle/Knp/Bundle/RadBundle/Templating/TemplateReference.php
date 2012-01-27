<?php

namespace Knp\Bundle\RadBundle\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference as BaseReference;

/**
 * TemplateReference.
 */
class TemplateReference extends BaseReference
{
    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        $controller = str_replace('\\', '/', $this->get('controller'));

        $path = (empty($controller) ? '' : $controller.'/').$this->get('name').'.'.$this->get('format').'.'.$this->get('engine');

        return empty($this->parameters['bundle']) ? 'views/'.$path : '@'.$this->get('bundle').'/views/'.$path;
    }
}
