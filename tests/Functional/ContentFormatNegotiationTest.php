<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableFormat;
use Symfony\Component\HttpFoundation\Request;

final class ContentFormatNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider anyFormatProvider
     */
    public function it_will_not_negotiate_if_not_configured(?string $header) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/no-format');
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
     * @dataProvider xmlProvider
     */
    public function it_negotiates_when_there_is_one_possibility(string $header, string $expected) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/xml');
        $request->headers->set('Accept', $header);

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expected, $response->headers->get('X-Negotiated-Format'));
        $this->assertSame($expected, $request->getRequestFormat('default'));
    }

    public function xmlProvider() : iterable
    {
        yield ['*/*', 'xml'];
        yield ['application/xml', 'xml'];
        yield ['application/*', 'xml'];
        yield ['application/json, application/xml;q=0.1', 'xml'];
    }

    /**
     * @test
     * @dataProvider xmlJsonProvider
     */
    public function it_negotiates_when_there_are_two_possibilities(string $header, string $expected) : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/xml-json');
        $request->headers->set('Accept', $header);

        $response = $kernel->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expected, $response->headers->get('X-Negotiated-Format'));
        $this->assertSame($expected, $request->getRequestFormat('default'));
    }

    public function xmlJsonProvider() : iterable
    {
        yield ['*/*', 'xml'];
        yield ['application/xml', 'xml'];
        yield ['application/*', 'xml'];
        yield ['application/json', 'json'];
        yield ['application/json, application/xml;q=0.1', 'json'];
        yield ['text/plain, application/json;q=0.1', 'json'];
    }

    /**
     * @test
     */
    public function it_fails_to_negotiate_when_there_is_one_possibility() : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/xml');
        $request->headers->set('Accept', 'application/json');

        $this->expectException(NotAcceptableFormat::class);

        $kernel->handle($request);
    }

    /**
     * @test
     */
    public function it_fails_to_negotiate_when_there_are_two_possibilities() : void
    {
        $kernel = static::getKernel('RouteLevel');

        $request = Request::create('/xml-json');
        $request->headers->set('Accept', 'text/plain');

        $this->expectException(NotAcceptableFormat::class);

        $kernel->handle($request);
    }
}
