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
use Symfony\Bridge\Doctrine\ManagerRegistry;

use Doctrine\Common\Persistence\ObjectManager;

use PHPCR\Util\NodeHelper;

use Kdm\ConfigBundle\Doctrine\Phpcr\Setting;

/**
 * @author Khang Minh <kminh@kdm.com>
 */
class SettingManager implements SettingManagerInterface
{
    protected $dm;

    protected $dr;

    protected $session; // phpcr session

    /**
     * @var array
     */
    protected $defaultSettings = array();

    /**
     * @var array
     */
    protected $settings = array();

    /**
     * @var array of Settings
     */
    protected $settingDocuments = array();

    protected $basePath = '/cms/settings';

    public function __construct(ManagerRegistry $mr, array $resourcePaths = array())
    {
        $this->dm = $mr->getManager(); // document manager
        $this->dr = $this->dm->getRepository(Setting::class); // document repository
        $this->session = $mr->getConnection();

        $this->loadDefaultSettings($resourcePaths);
        $this->loadSettings();
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->settings)) {
            $setting = $this->settings[$name];
        } else {
            throw new \RuntimeException(sprintf('No setting found for "%s".', $name));
        }

        // if there's at least one placeholder, we need to parse the value of setting
        if (strpos($setting, '{{') !== false) {
            $setting = preg_replace_callback(
                '/\{\{\s+([a-z0-9.-_]+)\s+\}\}/uis',
                function($matches) {
                    // recursively parse setting
                    try {
                        return $this->get($matches[1]);
                    } catch (\RuntimeException $e) {
                        // don't do anything if we can't parse the value, it
                        // might be a template placeholder
                        return $matches[0];
                    }
                },
                $setting
            );

            // cache parsed setting so we don't have to do this next time
            $this->settings[$name] = $setting;
        }

        return $setting;
    }

    public function getSettingDocument($name)
    {
        if (array_key_exists($name, $this->settingDocuments)) {
            $setting = $this->settingDocuments[$name];
        } else {
            return null;
            /* throw new \RuntimeException(sprintf('No setting found for "%s".', $name)); */
        }

        return $setting;
    }

    /**
     * {@inheritDoc}
     */
    public function getLastUpdatedTime($name)
    {
        $setting = $this->getSettingDocument($name);

        if (is_null($setting)) {
            return 0;
        }

        return $setting->getUpdatedAt()->format('U');
    }

    /**
     * {@inheritDoc}
     */
    public function getByGroupName($groupName, $rootGroupName = '')
    {
        $settings = array();

        foreach ($this->settings as $key => $value) {
            if (strpos($key, $groupName . '.') === 0) {
                $settings[str_replace($groupName . '.', '', $key)] = $value;
            } elseif ($key == $groupName) {
                // only single value
                return $value;
            }
        }

        return $this->unflatten($settings);
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
            $this->dm->persist($setting);
        }

        try {
            $this->dm->flush();
        } catch (\Exception $e) {
            return false;
        }

        // refresh preloaded settings
        $this->settings = $settings;

        // save successfully
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function saveGroup($groupName, array $newSettings = array())
    {
        $basePath = $this->basePath;
        $groupPath = $basePath . '/' . $groupName;

        if (!$rootGroup = $this->dm->find(null, $groupPath)) {
            throw new \DomainException(sprintf('Invalid setting group named "%s". Make sure you have initialized the group in database.', $groupName));
        }

        $newSettings = $this->flatten($newSettings, $groupName);

        $qb = $this->dm
            ->createQueryBuilder('s')
            ->fromDocument(Setting::class, 's')
            ->where()->descendant($rootGroup->getId(), 's')
            ->end()
        ;

        $dbSettings = $qb->getQuery()->execute();

        // update existing settings in db if needed
        foreach ($dbSettings as $dbSetting) {
            // build setting name from db setting's id (full path)
            $settingName = preg_replace('#^' . $basePath . '/#', '', $dbSetting->getId());
            $settingName = str_replace('/', '.', $settingName);

            if (isset($newSettings[$settingName])) {
                $dbSetting->setValue($newSettings[$settingName]);

                // unset here to determine which settings are new
                unset($newSettings[$settingName]);
            }
        }

        // add new settings if needed
        foreach ($newSettings as $name => $newSetting) {
            try {
                $currentSetting = $this->get($name);
            } catch (\Exception $e) {
                // this setting is not recognized, don't do anything
                continue;
            }

            // default parent document
            $parentDocument = $rootGroup;

            // each dot (.) in a setting name equals a group, for PHPCR we need
            // to create generic node for each group first before actually
            // saving the setting
            $settingName = preg_replace('#^' . $groupName . '\.#', '', $name, 1);

            if (strpos($settingName, '.') !== false) {
                // the last group is the actual setting name
                $groups = explode('.', $settingName);
                $settingName = array_pop($groups);
                $groupPath = implode('/', $groups);

                // create generic node
                $parentPath = NodeHelper::createPath($this->session, $rootGroup->getId() . '/' . $groupPath);
                $parentDocument = $this->dm->find(null, $parentPath->getPath());

                $this->session->save();
            }

            $dbSetting = new Setting($settingName, $newSetting);
            $dbSetting->setParentDocument($parentDocument);

            $this->dm->persist($dbSetting);
        }

        $this->dm->flush();
    }

    /**
     * Flatten the setting array
     *
     * @param mixed array $settings
     * @param string $prefix
     *
     * @return array
     */
    protected function flatten(array $settings, $prefix = '')
    {
        $separator = '.';
        $stack = $settings;
        $flattenedArray = array();

        while ($stack) {
            $build = array();
            list($key, $value) = each($stack);
            unset($stack[$key]);

            // if key is a group, i.e. has a '_' at the begining, remove it and
            // add it as a prefix
            if (strpos($key, '_') === 0) {
                $key = substr_replace($key, '', 0, 1);

                if (is_array($value)) {
                    foreach ($value as $subKey => $node) {
                        $groupPrefix = strpos($subKey, '_') === 0 ? '_' : '';
                        $subKey = !empty($groupPrefix) ? substr_replace($subKey, '', 0, 1) : $subKey;
                        $build[$groupPrefix . $key . $separator . $subKey] = $node;
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

    protected function buildNestedArray(array $groups, array &$stack, $value)
    {
        if (sizeof($groups) == 1) {
            $stack[$groups[0]] = $value;
            return;
        }

        $group = array_shift($groups);
        $groupName = '_' . $group;
        $stack[$groupName] = isset($stack[$groupName]) ? $stack[$groupName] : array();
        $this->buildNestedArray($groups, $stack[$groupName], $value);
    }

    /**
     * Unflatten the setting array
     *
     * @param mixed array $settings
     * @param string $prefix
     *
     * @return array
     */
    protected function unflatten(array $settings, $prefix = '')
    {
        $unflattenedArray = array();

        foreach ($settings as $name => $value) {
            if (strpos($name, '.') !== false) {
                $groups = explode('.', $name);
                $this->buildNestedArray($groups, $unflattenedArray, $value);

                continue;
            }

            $unflattenedArray[$name] = $value;
        }

        return $unflattenedArray;
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
        $settings = $this->dr->findBy(array('autoload' => true));

        foreach ($settings as $setting) {
            $settingName = preg_replace('#^' . $this->basePath . '/#', '', $setting->getId(), 1);
            $settingName = str_replace('/', '.', $settingName);

            $this->settings[$settingName] = $setting->getValue();
            $this->settingDocuments[$settingName] = $setting;
        }
    }
}
