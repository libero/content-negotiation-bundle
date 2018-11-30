<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use tests\Libero\ContentNegotiationBundle\Functional\App\Kernel;

abstract class FunctionalTestCase extends TestCase
{
    /** @var KernelInterface */
    private static $kernel;

    /**
     * @beforeClass
     */
    final public static function setUpKernel() : void
    {
        self::$kernel = self::createKernel();
    }

    /**
     * @afterClass
     */
    final public static function removeKernelCache() : void
    {
        (new Filesystem())->remove(self::$kernel->getCacheDir());
    }

    final public function getKernel() : KernelInterface
    {
        return self::$kernel;
    }

    private static function createKernel(array $options = []) : KernelInterface
    {
        $kernel = new Kernel(
            $options['environment'] ?? 'test',
            $options['debug'] ?? true
        );
        $kernel->boot();

        return $kernel;
    }
}
