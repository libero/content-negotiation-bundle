<?php

declare(strict_types=1);

namespace tests\Libero\ContentNegotiationBundle\Functional\App;

use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use function sys_get_temp_dir;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private $rootConfig;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);
    }

    public function registerBundles() : iterable
    {
        $bundles = require __DIR__.'/bundles.php';

        foreach ($bundles as $bundle) {
            yield new $bundle();
        }
    }

    public function getProjectDir() : string
    {
        return __DIR__;
    }

    public function getCacheDir() : string
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION."/cache/{$this->environment}";
    }

    public function getLogDir() : string
    {
        return sys_get_temp_dir().'/'.Kernel::VERSION.'/logs';
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader) : void
    {
        $container->register('logger', NullLogger::class);

        $loader->load(__DIR__.'/config.xml');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes) : void
    {
        $routes->import('routing.xml');
    }
}
