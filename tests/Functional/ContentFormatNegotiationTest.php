<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use Libero\ContentNegotiationBundle\Exception\NotAcceptableFormat;
use Symfony\Component\HttpFoundation\Request;

final class ContentFormatNegotiationTest extends FunctionalTestCase
{
    /**
     * @test
     * @dataProvider anyProvider
     */
    public function it_may_not_negotiate(?string $header) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/no-format');
        if (null !== $header) {
            $request->headers->set('Accept', $header);
        }

        $response = $kernel->handle($request);

        $this->assertNull($response->headers->get('X-Negotiated-Format'));
    }

    public function anyProvider() : iterable
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
    public function it_negotiates_any(string $header, string $expected) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/xml');
        $request->headers->set('Accept', $header);

        $response = $kernel->handle($request);

        $this->assertSame($expected, $response->headers->get('X-Negotiated-Format'));
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
     * @dataProvider xml2Provider
     */
    public function it_negotiates_any_2(string $header, string $expected) : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/xml-json');
        $request->headers->set('Accept', $header);

        $response = $kernel->handle($request);

        $this->assertSame($expected, $response->headers->get('X-Negotiated-Format'));
    }

    public function xml2Provider() : iterable
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
    public function it_negotiates_any_reject() : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/xml');
        $request->headers->set('Accept', 'application/json');

        $this->expectException(NotAcceptableFormat::class);

        $kernel->handle($request);
    }

    /**
     * @test
     */
    public function it_negotiates_any_reject2() : void
    {
        $kernel = static::getKernel();

        $request = Request::create('/xml-json');
        $request->headers->set('Accept', 'text/plain');

        $this->expectException(NotAcceptableFormat::class);

        $kernel->handle($request);
    }
}
