<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableFormat;
use Symfony\Component\HttpFoundation\Request;

final class PathFormatNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider anyFormatProvider
     */
    public function it_will_not_negotiate_if_not_configured(?string $header) : void
    {
        $kernel = static::getKernel('PathBased');

        $request = Request::create('/');
        if (null !== $header) {
            $request->headers->set('Accept', $header);
        } else {
            $request->headers->remove('Accept');
        }

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($response->headers->get('X-Negotiated-Format'));
        $this->assertSame('default', $request->getRequestFormat('default'));
    }

    public function anyFormatProvider() : iterable
    {
        yield [null];
        yield ['*/*'];
        yield ['application/xml'];
        yield ['application/json, application/xml;q=0.1'];
    }

    /**
     * @test
     * @dataProvider validNegotiateProvider
     */
    public function it_negotiates(string $path, string $header, ?string $expected) : void
    {
        $kernel = static::getKernel('PathBased');

        $request = Request::create($path);
        $request->headers->remove('Accept-Language');
        $request->headers->set('Accept', $header);

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expected, $response->headers->get('X-Negotiated-Format'));
        $this->assertSame($expected ?? 'default', $request->getRequestFormat('default'));
    }

    public function validNegotiateProvider() : iterable
    {
        yield ['/foo', '*/*', 'xml'];
        yield ['/foo/bar', '*/*', 'html'];
        yield ['/foo/bar/baz', '*/*', null];
        yield ['/foo', 'application/xml', 'xml'];
        yield ['/foo', 'application/json', 'json'];
        yield ['/foo/bar', 'text/html', 'html'];
        yield ['/foo/bar/baz', 'application/xml', null];
    }

    /**
     * @test
     */
    public function it_fails_to_negotiate_when_there_is_one_possibility() : void
    {
        $kernel = static::getKernel('PathBased');

        $request = Request::create('/foo/bar');
        $request->headers->remove('Accept-Language');
        $request->headers->set('Accept', 'application/xml');

        $this->expectException(NotAcceptableFormat::class);

        $kernel->handle($request);
    }

    /**
     * @test
     */
    public function it_fails_to_negotiate_when_there_are_two_possibilities() : void
    {
        $kernel = static::getKernel('PathBased');

        $request = Request::create('/foo');
        $request->headers->remove('Accept-Language');
        $request->headers->set('Accept', 'text/html');

        $this->expectException(NotAcceptableFormat::class);

        $kernel->handle($request);
    }
}
