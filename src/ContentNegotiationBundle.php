<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle;

use Libero\ContentNegotiationBundle\DependencyInjection\ContentNegotiationExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ContentNegotiationBundle extends Bundle
{
    protected function createContainerExtension() : ExtensionInterface
    {
        return new ContentNegotiationExtension();
    }
}
