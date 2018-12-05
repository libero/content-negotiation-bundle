<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\Exception;

use Exception;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use function implode;
use function sprintf;

abstract class NotAcceptable extends NotAcceptableHttpException
{
    private $acceptable;
    private $accept;

    public function __construct(
        AcceptHeader $accept,
        array $acceptable,
        ?string $message = null,
        ?Exception $previous = null,
        int $code = 0,
        array $headers = []
    ) {
        if (null === $message) {
            $message = sprintf("'%s' does not match '%s'", $accept, implode(', ', $acceptable));
        }

        parent::__construct($message, $previous, $code);
        $this->setHeaders($headers);

        $this->accept = $accept;
        $this->acceptable = $acceptable;
    }

    final public function getAccept() : AcceptHeader
    {
        return $this->accept;
    }

    /**
     * @return array<string>
     */
    final public function getAcceptable() : array
    {
        return $this->acceptable;
    }
}
