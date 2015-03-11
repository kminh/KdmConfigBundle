<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Twig\Loader;

use Kdm\ConfigBundle\Model\SettingManagerInterface;

/**
 * Allow loading config values as twig templates
 *
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class Config implements \Twig_LoaderInterface, \Twig_ExistsLoaderInterface
{
    protected $settings;

    public function __construct(SettingManagerInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritDoc}
     */
    public function getSource($name)
    {
        $name = $this->parseName($name);
        return $this->settings->get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function getCacheKey($name)
    {
        return $name;
    }

    /**
     * {@inheritDoc}
     */
    public function exists($name)
    {
        $name = $this->parseName($name);

        try {
            $this->settings->get($name);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh($name, $time)
    {
        $name = $this->parseName($name);
        return $this->settings->getLastUpdatedTime($name) <= $time;
    }

    protected function parseName($name)
    {
        $name = (string) $name;

        /* if (!preg_match('/^KdmSetting:/', $name)) { */
        /*     throw new \InvalidArgumentException('Invalid template name for config loader, should start with "KdmSetting:" followed by the setting\'s name.'); */
        /* } */

        return preg_replace('/^KdmSetting:/', '', $name, 1);
    }
}
