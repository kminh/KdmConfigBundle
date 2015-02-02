<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2014 Khang Minh <kminh@kdm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Model;

/**
 * Interface SettingManagerInterface
 *
 * @author Khang Minh <kminh@kdm.com>
 */
interface SettingManagerInterface
{
    /**
     * Gets an individual setting by name
     *
     * @param string $name
     * @return mixed
     */
    public function get($name);

    /**
     * Gets settings by group name
     *
     * @param string $groupName
     * @return array of Entity\Setting
     */
    public function getByGroupName($groupName);

    /**
     * Gets all settings
     *
     * @return array of Entity\Setting
     */
    public function getAll();

    /**
     * Saves one ore several setting(s). Save all settings if no settings
     * provided.
     *
     * @param array of setting name/value pairs
     */
    public function save(array $settings = array());
}

