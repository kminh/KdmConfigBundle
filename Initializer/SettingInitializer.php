<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Initializer;

use PHPCR\SessionInterface;
use PHPCR\Util\NodeHelper;

use Doctrine\Bundle\PHPCRBundle\Initializer\InitializerInterface;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;

class SettingInitializer implements InitializerInterface
{
    private $basePath;

    public function __construct($basePath = '/cms')
    {
        $this->basePath = $basePath;
    }

    public function init(ManagerRegistry $registry)
    {
        $session = $registry->getConnection();

        // create the 'settings' nodes
        NodeHelper::createPath($session, '/cms/settings');

        $session->save();
    }

    public function getName()
    {
        return 'Kdm Settings Initializer';
    }
}
