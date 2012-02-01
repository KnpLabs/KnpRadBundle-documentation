<?php

/*
 * This file is part of the KnpRadBundle package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\Bundle\RadBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

use Knp\Bundle\RadBundle\Bundle\ApplicationBundle;

/**
 * Adds Response event listener to render no-Response
 * controller results (arrays).
 */
class ViewListener
{
    private $kernel;
    private $templating;
    private $engine;

    /**
     * Initializes listener.
     *
     * @param KernelInterface $kernel     Kernel instance
     * @param EngineInterface $templating Templating engine
     * @param string          $engine     Default engine name
     */
    public function __construct(KernelInterface $kernel, EngineInterface $templating, $engine)
    {
        $this->kernel     = $kernel;
        $this->templating = $templating;
        $this->engine     = $engine;
    }

    /**
     * Patches response on empty responses.
     *
     * @param GetResponseForControllerResultEvent $event Event instance
     */
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
