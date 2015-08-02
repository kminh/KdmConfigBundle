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

use Doctrine\ODM\PHPCR\HierarchyInterface;

use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;

use Kdm\CmfBundle\Translation\LocaleAwareEntity;

use Kdm\ConfigBundle\Model\SettingInterface;

/**
 * @author Khang Minh <kminh@kdm.com>
 */
class Setting implements SettingInterface, HierarchyInterface, TranslatableInterface
{
    use LocaleAwareEntity;

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
     * @var mixed object|null
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

    protected $createdAt;

    protected $updatedAt;

    public function __construct($name = '', $value = '')
    {
        if (is_null($name) || !is_string($name)) {
            throw new \InvalidArgumentException('Setting must have a valid name');
        }

        $this->name  = $name;
        $this->value = $value;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setGroupName($name)
    {
        $this->groupName = $name;

        return $this;
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

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentDocument()
    {
        return $this->getGroup();
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return $this->getParentDocument();
    }

    /**
     * {@inheritDoc}
     */
    public function setParentDocument($parent)
    {
        return $this->setGroup($parent);
    }

    /**
     * {@inheritDoc}
     */
    public function setParent($parent)
    {
        return $this->setParentDocument($parent);
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

        return $this;
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

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function needReload()
    {
        return (bool) $this->needReload;
    }

    /**
     * Sets createdAt.
     *
     * @param  DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $createdAt = is_null($createdAt) ? new \DateTime() : $createdAt;

        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @param  DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Returns updatedAt.
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function onPrePersist()
    {
        $now = new \DateTime();

        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);
    }

    public function onPreUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }
}
