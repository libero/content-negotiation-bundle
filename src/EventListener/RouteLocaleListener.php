<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\EventListener;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableLocale;
use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use function explode;
use function implode;

final class RouteLocaleListener
{
    private $negotiator;
    private $router;

    public function __construct(LanguageNegotiator $negotiator, RouterInterface $router)
    {
        $this->negotiator = $negotiator;
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event) : void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        $routeName = $request->attributes->get('_route');
        $route = $this->router->getRouteCollection()->get($routeName);

        if (!$route || !$route->getRequirement('_locale')) {
            return;
        }

        $header = implode(', ', (array) $request->headers->get('Accept-Language', null, false));
        if (empty($header)) {
            $header = '*';
        }

        $locales = explode('|', $route->getRequirement('_locale'));

        $match = $this->negotiator->getBest($header, $locales);

        if (!$match instanceof AcceptLanguage) {
            throw new NotAcceptableLocale(AcceptHeader::fromString($header), $locales);
        }

        $request->setLocale($match->getNormalizedValue());
    }
}
