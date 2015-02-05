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

use Symfony\Component\Finder\Finder;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author Khang Minh <kminh@kdm.com>
 */
class SettingManager implements SettingManagerInterface
{
    protected $om;

    /**
     * @var array
     */
    protected $defaultSettings = array();

    /**
     * @var array
     */
    protected $settings = array();

    public function __construct(ObjectManager $om, array $resourcePaths = array())
    {
        $this->om = $om;

        $this->loadDefaultSettings($resourcePaths);
        $this->loadSettings();
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if (isset($this->settings[$name])) {
            return $this->settings[$name];
        }

        // try getting the setting from database

        throw new \RuntimeException(sprintf('No setting found for "%s".', $name));
    }

    /**
     * {@inheritDoc}
     */
    public function getByGroupName($groupName)
    {
        $settings = array();

        foreach ($this->settings as $key => $value) {
            if (strpos($key, $groupName . '.') === 0) {
                $settings[str_replace($groupName . '.', '', $key)] = $value;
            }
        }

        return $settings;
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

    /**
     * Flatten the setting array
     *
     * @param mixed array $settings
     * @param string $prefix
     *
     * @return string
     */
    protected function flatten(array $settings, $prefix = '')
    {
        $separator = '.';
        $stack = $settings;
        $flattenedArray = array();

        while ($stack) {
            list($key, $value) = each($stack);
            unset($stack[$key]);

            // if key is a group, i.e. has a '_' at the begining, remove it and
            // add it as a prefix
            if (strpos($key, '_') === 0) {
                $key = substr_replace($key, '', 0, 1);

                if (is_array($value)) {
                    foreach ($value as $subKey => $node) {
                        $build[$key . $separator . $subKey] = $node;
                    }

                    $stack = $build + $stack;

                    continue;
                }
            } else {
                $flattenedArray[$prefix . $separator . $key] = $value;
            }
        }

        return $flattenedArray;
    }

    /**
     * Load default settings from certain resources
     *
     * @param array $resourcePaths an array of paths to locate resources
     */
    protected function loadDefaultSettings(array $resourcePaths)
    {
        $finder = new Finder();

        foreach ($resourcePaths as $path) {
            // get all setting files inside the specified resource path and
            // include them as setting variables
            $settingFiles = $finder
                ->files()
                ->in($path)
                ->name('*.php')
                ->depth('== 0');

            foreach ($settingFiles as $file) {
                $settings = include $file;
                if (!is_array($settings)) {
                    throw new \InvalidArgumentException('Provided default settings resource is invalid, please use a PHP file that returns an array.');
                }

                $settingPrefix = str_replace('.php', '', $file->getFileName());
                $this->defaultSettings = array_merge($this->defaultSettings, $this->flatten($settings, $settingPrefix));
            }
        }

        $this->settings = $this->defaultSettings;
    }

    /**
     * Load settings that are marked as 'autoload' from database and merge
     * them with default settings
     */
    protected function loadSettings()
    {
        $repo = $this->om->getRepository('KdmConfigBundle:Setting');

        $settings = $repo->findBy(array('autoload' => true));

        foreach ($settings as $setting) {
            $this->settings[$setting->getName()] = $setting;
        }
    }
}
