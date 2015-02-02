<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2014 Khang Minh <kminh@kdm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Doctrine\Phpcr;

use Kdm\ConfigBundle\Model\SettingInterface;

/**
 * @author Khang Minh <kminh@kdm.com>
 */
class Setting implements SettingInterface
{
    /**
     * Setting id.
     *
     * @var string
     */
    protected $id;

    /**
     * Setting name.
     *
     * @var string
     */
    protected $name;

    /**
     * Setting value.
     *
     * @var string
     */
    protected $value;

    /**
     * Setting group name
     *
     * @var string
     */
    protected $groupName;

    /**
     * Setting group
     *
     * @var
     */
    protected $group;

    /**
     * Whether to autoload this setting.
     *
     * @var bool
     */
    protected $autoload = true;

    /**
     * Whether we need to reload this setting from storage.
     *
     * @var bool
     */
    protected $needReload = false;

    public function __construct($name = '', $value = '')
    {
        if (is_null($name) || !is_string($name)) {
            throw new \InvalidArgumentException('Setting must have a valid name');
        }

        $this->name  = $name;
        $this->value = $value;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroupName($name)
    {
        $this->groupName = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupName()
    {
        return $this->groupName;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->needReload = false;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function save($newValue)
    {
        if ($this->value !== $newValue) {
            $this->setValue($newValue);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function reload()
    {
        $this->needReload = true;
    }

    /**
     * {@inheritDoc}
     */
    public function needReload()
    {
        return (bool) $this->needReload;
    }
}
