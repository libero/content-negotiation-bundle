<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\Negotiator;

use Symfony\Component\HttpFoundation\RequestMatcher;
use function array_filter;
use function explode;

/**
 * @internal
 */
final class NegotiationRule extends RequestMatcher
{
    private $optional;
    private $path;
    private $priorities;

    public function __construct(string $path, string $priorities, bool $optional = false)
    {
        parent::__construct($path);

        $this->path = $path;
        $this->priorities = array_filter(explode('|', $priorities));
        $this->optional = $optional;
    }

    public function isOptional() : bool
    {
        return $this->optional;
    }

    public function getPriorities() : array
    {
        return $this->priorities;
    }
}
