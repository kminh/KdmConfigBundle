<?php

/**
 * This file is part of the Lvs package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Model;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
interface SettingAdminInterface
{
    /**
     * Get the setting group name associated with this admin
     *
     * @return string
     */
    public function getSettingGroupName();
}
