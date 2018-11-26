<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle;

use Libero\ContentNegotiationBundle\ContentNegotiationBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

final class ContentNegotiationBundleTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_bundle() : void
    {
        $bundle = new ContentNegotiationBundle();

        $this->assertInstanceOf(BundleInterface::class, $bundle);
    }
}
