<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableLocale;
use Symfony\Component\HttpFoundation\Request;

final class ContentLocaleNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider anyLocaleProvider
     */
    public function it_will_not_negotiate_if_not_configured(?string $header) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/no-locale');
        if (null !== $header) {
            $request->headers->set('Accept-Language', $header);
        }

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('und', $response->headers->get('X-Negotiated-Locale'));
        $this->assertSame('und', $request->getLocale());
    }

    public function anyLocaleProvider() : iterable
    {
        yield [null];
        yield ['*'];
        yield ['en'];
        yield ['fr, en;q=0.1'];
    }

    /**
     * @test
     * @dataProvider enProvider
     */
    public function it_negotiates_when_there_is_one_possibility(string $header) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/en');
        $request->headers->set('Accept-Language', $header);

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('en', $response->headers->get('X-Negotiated-Locale'));
        $this->assertSame('en', $request->getLocale());
    }

    public function enProvider() : iterable
    {
        yield ['*'];
        yield ['en'];
        yield ['en-GB, en;q=0.1'];
        yield ['fr, en;q=0.1'];
    }

    /**
     * @test
     * @dataProvider enFrProvider
     */
    public function it_negotiates_when_there_are_two_possibilities(string $header, string $expected) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/en-fr');
        $request->headers->set('Accept-Language', $header);

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expected, $response->headers->get('X-Negotiated-Locale'));
        $this->assertSame($expected, $request->getLocale());
    }

    public function enFrProvider() : iterable
    {
        yield ['*', 'en'];
        yield ['en', 'en'];
        yield ['fr', 'fr'];
        yield ['fr, en;q=0.1', 'fr'];
        yield ['de, fr;q=0.1', 'fr'];
    }

    /**
     * @test
     * @dataProvider enRejectProvider
     */
    public function it_fails_to_negotiate_when_there_is_one_possibility(string $header) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/en');
        $request->headers->set('Accept-Language', $header);

        $this->expectException(NotAcceptableLocale::class);

        $kernel->handle($request);
    }

    public function enRejectProvider() : iterable
    {
        yield ['fr'];
        yield ['en-GB'];
        yield ['fr, en-GB;q=0.1'];
    }

    /**
     * @test
     * @dataProvider enFrRejectProvider
     */
    public function it_fails_to_negotiate_when_there_are_two_possibilities(string $header) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/en-fr');
        $request->headers->set('Accept-Language', $header);

        $this->expectException(NotAcceptableLocale::class);

        $kernel->handle($request);
    }

    public function enFrRejectProvider() : iterable
    {
        yield ['de'];
        yield ['en-GB'];
        yield ['de, en-GB;q=0.1'];
    }
}
