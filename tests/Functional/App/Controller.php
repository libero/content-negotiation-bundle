<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional\App;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class Controller
{
    public function __invoke(Request $request) : Response
    {
        $response = new Response();

        $response->headers->set('X-Negotiated-Locale', $request->getLocale());
        $response->headers->set('X-Negotiated-Format', $request->getRequestFormat(null));

        return $response;
    }
}
