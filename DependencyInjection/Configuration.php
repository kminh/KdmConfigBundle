<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('kdm_config');

        $rootNode
            ->children()
                ->arrayNode('setting_paths')
                    ->isRequired()
                    ->info('Absolute paths to where setting files can be found. Nesting paths is not supported and only support PHP setting files.')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('persistence')
                    ->children()
                        ->arrayNode('phpcr')
                            ->children()
                                ->scalarNode('setting_base_path')
                                    ->defaultValue('cms/settings') // todo recheck cmf_core configuration
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
