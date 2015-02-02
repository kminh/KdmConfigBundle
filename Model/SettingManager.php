<?php

/**
 * This file is part of the Uni3D package.
 *
 * (c) 2014 Khang Minh <kminh@kdm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Khang Minh <kminh@kdm.com>
 */
class SettingManager implements SettingManagerInterface
{
    protected $om;

    protected $settings;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        // preload all autoload settings
        $this->preload();
    }

    /**
     * {@inheritDoc}
     */
    protected function preload()
    {
        $repo = $this->om->getRepository('Kdm3dConfigBundle:Setting');

        $settings = $repo->findBy(array('autoload' => true));

        foreach ($settings as $setting) {
            $this->settings[$setting->getName()] = $setting;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }

        throw new \RuntimeException(sprintf('No setting found for "%s".', $name));
    }

    /**
     * {@inheritDoc}
     */
    public function getByGroupName($groupName)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getAll()
    {
        return $this->settings;
    }

    /**
     * {@inheritDoc}
     */
    public function save(array $settings = array())
    {
        $settings = sizeof($settings) == 0 ? $this->getAll() : $settings;

        foreach ($settings as $setting) {
            /* $setting->save(); */
            $this->om->persist($setting);
        }

        try {
            $this->om->flush();
        } catch (\Exception $e) {
            return false;
        }

        // refresh preloaded settings
        $this->settings = $settings;

        // save successfully
        return true;
    }
}
