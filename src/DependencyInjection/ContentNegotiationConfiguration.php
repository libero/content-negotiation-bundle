<?php

declare(strict_types=1);

namespace Libero\ContentNegotiationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class ContentNegotiationConfiguration implements ConfigurationInterface
{
    private $rootName;

    public function __construct(string $rootName)
    {
        $this->rootName = $rootName;
    }

    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root($this->rootName);
        $rootNode
            ->fixXmlConfig('format')
            ->fixXmlConfig('locale')
            ->children()
                ->append($this->getFormatsDefinition())
                ->append($this->getLocalesDefinition())
            ->end()
        ;

        return $treeBuilder;
    }

    private function getFormatsDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $formatsNode */
        $formatsNode = $builder->root('formats');
        $formatsNode
            ->info('Formats.')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('path')
                        ->isRequired()
                        ->info('Path.')
                    ->end()
                    ->scalarNode('priorities')
                        ->isRequired()
                        ->info('Priorities.')
                    ->end()
                ->end()
            ->end()
        ;

        return $formatsNode;
    }

    private function getLocalesDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $localesNode */
        $localesNode = $builder->root('locales');
        $localesNode
            ->info('Locales.')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('path')
                        ->isRequired()
                        ->info('Path.')
                    ->end()
                    ->scalarNode('priorities')
                        ->isRequired()
                        ->info('Priorities.')
                    ->end()
                ->end()
            ->end()
        ;

        return $localesNode;
    }
}
