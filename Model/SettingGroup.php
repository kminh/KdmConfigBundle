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
abstract class SettingGroup
{
    /**
     * Name of the setting group
     *
     * @var string
     */
    protected $groupName;

    /**
     * @var SettingManagerInterface
     */
    protected $settingManager;

    protected $settings;

    public function __construct(SettingManagerInterface $settingManager)
    {
        if (is_null($this->groupName)) {
            throw \InvalidArgumentException('Implementation class should have a valid group name');
        }

        $this->settingManager = $settingManager;
        $this->settings = $this->settingManager->getByGroupName($this->groupName);
    }

    public function save()
    {
        $this->settingManager->save($this->settings);
    }
}
