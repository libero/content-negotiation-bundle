<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\EventListener;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableFormat;
use Libero\ContentNegotiationBundle\Negotiator\NegotiationRule;
use Negotiation\Accept;
use Negotiation\Negotiator;
use RuntimeException;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use function array_merge;
use function array_reduce;
use function array_unique;
use function count;
use function implode;

final class PathFormatListener
{
    private $negotiator;
    private $rules;

    /**
     * @param NegotiationRule[] $rules
     */
    public function __construct(Negotiator $negotiator, array $rules)
    {
        $this->negotiator = $negotiator;
        $this->rules = $rules;
    }

    public function onKernelRequest(GetResponseEvent $event) : void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        foreach ($this->rules as $rule) {
            if (!$rule->matches($request)) {
                continue;
            }

            if (0 === count($rule->getPriorities())) {
                return;
            }

            $formats = array_reduce(
                $rule->getPriorities(),
                function (array $carry, string $format) use ($request) : array {
                    $mediaTypes = $request->getMimeTypes($format);

                    if (empty($mediaTypes)) {
                        throw new RuntimeException("Unknown format '{$format}'");
                    }

                    return array_merge($carry, $mediaTypes);
                },
                []
            );

            $request->attributes->set(
                '_format_possibilities',
                array_unique(
                    array_merge(
                        $request->attributes->get('_format_possibilities', []),
                        $formats
                    )
                )
            );
            $request->attributes->set('_format_rule', $rule);

            $match = $this->negotiator->getBest($this->getAcceptHeader($request), $formats);

            if ($match instanceof Accept) {
                $normalized = $match->getNormalizedValue();
                $request->setRequestFormat($request->getFormat($normalized) ?? $normalized);

                return;
            }

            if (!$rule->isOptional()) {
                return;
            }
        }
    }

    public function onKernelRequestLate(GetResponseEvent $event) : void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        /** @var NegotiationRule|null $rule */
        $rule = $request->attributes->get('_format_rule');

        if ($rule && !$rule->isOptional() && !$request->getRequestFormat(null)) {
            throw new NotAcceptableFormat($this->getAcceptHeader($request), $request->get('_format_possibilities'));
        }
    }

    private function getAcceptHeader(Request $request) : AcceptHeader
    {
        $header = implode(', ', (array) $request->headers->get('Accept', null, false));
        if (empty($header)) {
            $header = '*/*';
        }

        return AcceptHeader::fromString($header);
    }
}
