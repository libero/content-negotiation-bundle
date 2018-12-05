<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

final class DefaultLocaleListener
{
    private $defaultLocale;

    public function __construct(string $defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event) : void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $event->getRequest()->setDefaultLocale($this->defaultLocale);
    }
}
