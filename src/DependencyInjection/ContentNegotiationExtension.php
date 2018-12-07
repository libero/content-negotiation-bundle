<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\DependencyInjection;

use Libero\ContentNegotiationBundle\EventListener\PathFormatListener;
use Libero\ContentNegotiationBundle\EventListener\PathLocaleListener;
use Libero\ContentNegotiationBundle\Negotiator\NegotiationRule;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use function md5;
use function serialize;

final class ContentNegotiationExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $rules = [];
        foreach ($config['formats'] as $ruleConfig) {
            $rule = new Definition(NegotiationRule::class);
            $rule->addArgument($ruleConfig['path']);
            $rule->addArgument($ruleConfig['priorities']);
            $rule->addArgument($ruleConfig['optional']);
            $container->setDefinition($id = PathFormatListener::class.'.'.md5(serialize($ruleConfig)), $rule);

            $rules[] = new Reference($id);
        }

        $container->findDefinition(PathFormatListener::class)
            ->setArgument(1, $rules);

        $rules = [];
        foreach ($config['locales'] as $ruleConfig) {
            $rule = new Definition(NegotiationRule::class);
            $rule->addArgument($ruleConfig['path']);
            $rule->addArgument($ruleConfig['priorities']);
            $rule->addArgument($ruleConfig['optional']);
            $container->setDefinition($id = PathLocaleListener::class.'.'.md5(serialize($ruleConfig)), $rule);

            $rules[] = new Reference($id);
        }

        $container->findDefinition(PathLocaleListener::class)
            ->setArgument(1, $rules);
    }

    public function getConfiguration(array $config, ContainerBuilder $container) : ConfigurationInterface
    {
        return new ContentNegotiationConfiguration($this->getAlias());
    }

    public function getNamespace() : string
    {
        return 'http://libero.pub/schema/content-negotiation-bundle';
    }

    public function getXsdValidationBasePath() : string
    {
        return __DIR__.'/../Resources/config/schema/content-negotiation-bundle';
    }

    public function getAlias() : string
    {
        return 'content_negotiation';
    }
}
