<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use tests\Libero\ContentNegotiationBundle\Functional\App\Kernel;

abstract class FunctionalTestCase extends TestCase
{
    /** @var Filesystem */
    private static $filesystem;

    /** @var KernelInterface */
    private static $kernel;

    public static function setUpBeforeClass() : void
    {
        self::$filesystem = new Filesystem();
        parent::setUpBeforeClass();
        self::$kernel = self::createKernel();
    }

    public static function tearDownAfterClass() : void
    {
        parent::tearDownAfterClass();

        self::$filesystem->remove(self::$kernel->getCacheDir());
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
