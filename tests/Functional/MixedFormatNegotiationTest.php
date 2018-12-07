<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableFormat;
use Symfony\Component\HttpFoundation\Request;

final class MixedFormatNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_uses_the_route_requirement() : void
    {
        $kernel = static::getKernel('Mixed');

        $request = Request::create('/format');
        $request->headers->set('Accept', 'application/xml');

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('xml', $response->headers->get('X-Negotiated-Format'));
    }

    /**
     * @test
     */
    public function it_does_not_use_the_path_level_requirement() : void
    {
        $kernel = static::getKernel('Mixed');

        $request = Request::create('/format');
        $request->headers->set('Accept', 'text/plain');

        $this->expectException(NotAcceptableFormat::class);

        $kernel->handle($request);
    }
}
