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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class KdmConfigExtension extends ConfigurableExtension
{
    public function loadConfiguration(ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('parameters.yml');
        $loader->load('services.yml');
    }

    protected function loadInternal(array $configs, ContainerBuilder $container)
    {
        $this->loadConfiguration($container);

        // path to where config files are stored
        if (!empty($configs['setting_paths'])) {
            $container->setParameter('kdm.config.setting_paths', $configs['setting_paths']);
        }
    }
}
