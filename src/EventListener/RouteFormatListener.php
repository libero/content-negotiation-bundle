<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\EventListener;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableFormat;
use Negotiation\Accept;
use Negotiation\Negotiator;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;
use function array_merge;
use function array_reduce;
use function explode;
use function implode;

final class RouteFormatListener
{
    private $negotiator;
    private $router;

    public function __construct(Negotiator $negotiator, RouterInterface $router)
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

        if (!$route) {
            return;
        }

        $header = implode(', ', (array) $request->headers->get('Accept', null, false));
        if (empty($header)) {
            $header = '*/*';
        }

        if ($route->getRequirement('_format')) {
            $formats = array_reduce(
                explode('|', $route->getRequirement('_format')),
                function (array $carry, string $format) use ($request) : array {
                    return array_merge($carry, $request->getMimeTypes($format));
                },
                []
            );

            /** @var Accept|null $match */
            $match = $this->negotiator->getBest($header, $formats);

            if (!$match) {
                throw new NotAcceptableFormat(AcceptHeader::fromString($header), $formats);
            }

            $normalized = $match->getNormalizedValue();
            $request->setRequestFormat($request->getFormat($normalized) ?? $normalized);

            return;
        }
    }
}
