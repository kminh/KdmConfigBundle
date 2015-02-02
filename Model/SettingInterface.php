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
 * @author Khang Minh <kminh@kdm.com>
 */
interface SettingInterface
{
    /**
     * Sets a setting group for this setting
     *
     * @param string $group
     */
    public function setGroupName($name);

    /**
     * Gets the setting group of this setting
     *
     * @return string
     */
    public function getGroupName();

    /**
     * Sets a setting group for this setting
     *
     * @param mixed $group
     */
    public function setGroup($group);

    /**
     * Gets the setting group of this setting
     *
     * @return mixed
     */
    public function getGroup();

    /**
     * Gets setting name
     *
     * @return string
     */
    public function getName();

    /**
     * Sets setting name
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Sets the value for this setting, no update should be made to setting in
     * storage until SettingInterface::save is called
     *
     * @param mixed $value
     */
    public function setValue($value);

    /**
     * Gets the value of this setting
     */
    public function getValue();

    /**
     * Saves a setting to storage, should not issue an update if new value and
     * old value are the same.
     *
     * @param mixed $newValue the value to save
     */
    public function save($newValue);

    /**
     * Reload a setting from storage
     */
    public function reload();

    /**
     * Whether this setting needs reloading from storage
     *
     * @return bool
     */
    public function needReload();
}
