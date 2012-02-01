<?php

namespace Knp\Bundle\RadBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;

class ViewListener
{
    private $kernel;
    private $templating;
    private $engine;

    public function __construct(KernelInterface $kernel, EngineInterface $templating, $engine)
    {
        $this->kernel     = $kernel;
        $this->templating = $templating;
        $this->engine     = $engine;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request    = $event->getRequest();
        $attributes = $request->attributes;

        if (false !== strpos($attributes->get('_controller'), '::')) {
            list($class, $method) = explode('::', $attributes->get('_controller'));

            return $event->setResponse($this->templating->renderResponse(
                sprintf('%s:%s.%s.%s',
                    substr(basename(str_replace('\\', '/', $class)), 0, -10),
                    $method,
                    $request->getRequestFormat(),
                    $this->engine
                ),
                $event->getControllerResult()
            ));
        }
    }
}
