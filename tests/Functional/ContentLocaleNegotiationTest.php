<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableLocale;
use Symfony\Component\HttpFoundation\Request;

final class ContentLocaleNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider anyProvider
     */
    public function it_may_not_negotiate(?string $header) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/no-locale');
        if (null !== $header) {
            $request->headers->set('Accept-Language', $header);
        }

        $response = $kernel->handle($request);

        $this->assertSame('und', $response->headers->get('X-Negotiated-Locale'));
    }

    public function anyProvider() : iterable
    {
        yield [null];
        yield ['*'];
        yield ['en'];
        yield ['fr, en;q=0.1'];
    }

    /**
     * @test
     * @dataProvider xmlProvider
     */
    public function it_negotiates_any(string $header) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/en');
        $request->headers->set('Accept-Language', $header);

        $response = $kernel->handle($request);

        $this->assertSame('en', $response->headers->get('X-Negotiated-Locale'));
    }

    public function xmlProvider() : iterable
    {
        yield ['*'];
        yield ['en'];
        yield ['en-GB, en;q=0.1'];
        yield ['fr, en;q=0.1'];
    }

    /**
     * @test
     * @dataProvider xml2Provider
     */
    public function it_negotiates_any_2(string $header, string $expected) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/en-fr');
        $request->headers->set('Accept-Language', $header);

        $response = $kernel->handle($request);

        $this->assertSame($expected, $response->headers->get('X-Negotiated-Locale'));
    }

    public function xml2Provider() : iterable
    {
        yield ['*', 'en'];
        yield ['en', 'en'];
        yield ['fr', 'fr'];
        yield ['fr, en;q=0.1', 'fr'];
        yield ['de, fr;q=0.1', 'fr'];
    }

    /**
     * @test
     * @dataProvider rejectProvider
     */
    public function it_negotiates_any_reject(string $header) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/en');
        $request->headers->set('Accept-Language', $header);

        $this->expectException(NotAcceptableLocale::class);

        $kernel->handle($request);
    }

    public function rejectProvider() : iterable
    {
        yield ['fr'];
        yield ['en-GB'];
        yield ['fr, en-GB;q=0.1'];
    }

    /**
     * @test
     * @dataProvider rejectProvider2
     */
    public function it_negotiates_any_reject2(string $header) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/en-fr');
        $request->headers->set('Accept-Language', $header);

        $this->expectException(NotAcceptableLocale::class);

        $kernel->handle($request);
    }

    public function rejectProvider2() : iterable
    {
        yield ['de'];
        yield ['en-GB'];
        yield ['de, en-GB;q=0.1'];
    }
}
