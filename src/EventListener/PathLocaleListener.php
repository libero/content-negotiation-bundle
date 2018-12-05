<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\EventListener;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableLocale;
use Libero\ContentNegotiationBundle\Negotiator\NegotiationRule;
use Negotiation\AcceptLanguage;
use Negotiation\LanguageNegotiator;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use function array_merge;
use function array_unique;
use function count;
use function implode;

final class PathLocaleListener
{
    private $negotiator;
    private $rules;

    public function __construct(LanguageNegotiator $negotiator, array $rules)
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
            /** @var NegotiationRule $rule */
            if (!$rule->matches($request)) {
                continue;
            }

            if (0 === count($rule->getPriorities())) {
                return;
            }

            $request->attributes->set(
                '_locale_possibilities',
                array_unique(
                    array_merge(
                        $request->attributes->get('_locale_possibilities', []),
                        $rule->getPriorities()
                    )
                )
            );
            $request->attributes->set('_locale_rule', $rule);

            $match = $this->negotiator->getBest($this->getAcceptLanguageHeader($request), $rule->getPriorities());

            if ($match instanceof AcceptLanguage) {
                $request->attributes->set('_locale_set', true);
                $request->setLocale($match->getNormalizedValue());

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
        $rule = $request->attributes->get('_locale_rule');

        if ($rule && !$rule->isOptional() && !$request->attributes->get('_locale_set', false)) {
            throw new NotAcceptableLocale(
                $this->getAcceptLanguageHeader($request),
                $request->get('_locale_possibilities')
            );
        }
    }

    private function getAcceptLanguageHeader(Request $request) : AcceptHeader
    {
        $header = implode(', ', (array) $request->headers->get('Accept-Language', null, false));
        if (empty($header)) {
            $header = '*';
        }

        return AcceptHeader::fromString($header);
    }
}
