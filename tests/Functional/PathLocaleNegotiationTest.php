<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableLocale;
use Symfony\Component\HttpFoundation\Request;

final class PathLocaleNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider anyLocaleProvider
     */
    public function it_will_not_negotiate_if_not_configured(?string $header) : void
    {
        $kernel = static::getKernel('PathBased');

        $request = Request::create('/');
        if (null !== $header) {
            $request->headers->set('Accept-Language', $header);
        } else {
            $request->headers->remove('Accept-Language');
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
        yield ['de, en;q=0.1'];
    }

    /**
     * @test
     * @dataProvider validNegotiateProvider
     */
    public function it_negotiates(string $path, string $header, string $expected) : void
    {
        $kernel = static::getKernel('PathBased');

        $request = Request::create($path);
        $request->headers->remove('Accept');
        $request->headers->set('Accept-Language', $header);

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expected, $response->headers->get('X-Negotiated-Locale'));
        $this->assertSame($expected, $request->getLocale());
    }

    public function validNegotiateProvider() : iterable
    {
        // Accepts en|fr
        yield ['/foo', '*', 'en'];
        yield ['/foo', 'en', 'en'];
        yield ['/foo', 'fr', 'fr'];

        // Accepts de, falls back to en|fr
        yield ['/foo/bar', '*', 'de'];
        yield ['/foo/bar', 'de', 'de'];
        yield ['/foo/bar', 'en', 'en'];
        yield ['/foo/bar', 'fr', 'fr'];

        // No negotiation
        yield ['/foo/bar/baz', '*', 'und'];
        yield ['/foo/bar/baz', 'en', 'und'];
    }

    /**
     * @test
     */
    public function it_can_fail_to_negotiate() : void
    {
        $kernel = static::getKernel('PathBased');

        $request = Request::create('/foo');
        $request->headers->remove('Accept');
        $request->headers->set('Accept-Language', 'de');

        $this->expectException(NotAcceptableLocale::class);

        $kernel->handle($request);
    }
}
