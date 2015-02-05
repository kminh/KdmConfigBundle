<?php

/**
 * This file is part of the Uni3D package.
 *
 * (c) 2014 Khang Minh <kminh@kdm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass;

/**
 * This bundle handles saving and retrieving configuration and settings
 *
 * @author Khang Minh <kminh@kdm.com>
 */
class KdmConfigBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        if (class_exists('Doctrine\Bundle\PHPCRBundle\DependencyInjection\Compiler\DoctrinePhpcrMappingsPass')) {
            $container->addCompilerPass(
                DoctrinePhpcrMappingsPass::createYamlMappingDriver(
                    [realpath(__DIR__ . '/Resources/config/doctrine-phpcr') => 'Kdm\ConfigBundle\Doctrine\Phpcr'],
                    [],
                    false,
                    array('KdmConfigBundle' => 'Kdm\ConfigBundle\Doctrine\Phpcr')
                )
            );
        }
    }
}
