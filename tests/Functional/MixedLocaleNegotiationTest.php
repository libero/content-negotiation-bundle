<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableLocale;
use Symfony\Component\HttpFoundation\Request;

final class MixedLocaleNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     */
    public function it_uses_the_route_requirement() : void
    {
        $kernel = static::getKernel('Mixed');

        $request = Request::create('/locale');
        $request->headers->set('Accept-Language', 'en');

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('en', $response->headers->get('X-Negotiated-Locale'));
    }

    /**
     * @test
     */
    public function it_does_not_use_the_path_level_requirement() : void
    {
        $kernel = static::getKernel('Mixed');

        $request = Request::create('/locale');
        $request->headers->set('Accept-Language', 'de');

        $this->expectException(NotAcceptableLocale::class);

        $kernel->handle($request);
    }
}
