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
     * @return array of settings with group name removed. For example a setting
     *         with name 'general.site.title' when retrieved via
     *         getByGroupName('general') would be returned as 'site.title'.
     */
    public function getByGroupName($groupName);

    /**
     * Gets all settings
     *
     * @return array
     */
    public function getAll();

    /**
     * Saves one or several setting(s). Save all settings if no settings
     * provided.
     *
     * @param array of setting name/value pairs
     */
    public function save(array $settings = array());

    /**
     * Saves a group of setting.
     *
     * @param string $groupName name of the group to save
     * @param array $newSettings array of setting name/value pairs to be updated with
     */
    public function saveGroup($groupName, array $newSettings = array());
}
