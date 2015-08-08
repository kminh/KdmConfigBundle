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
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Bridge\Doctrine\ManagerRegistry;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\PHPCR\DocumentManager;

use PHPCR\Util\NodeHelper;

use Kdm\ConfigBundle\Doctrine\Phpcr\Setting;
use Kdm\ConfigBundle\Doctrine\Phpcr\I18nSetting;

/**
 * @author Khang Minh <kminh@kdm.com>
 */
class SettingManager implements SettingManagerInterface
{
    /**
     * @var DocumentManager
     */
    protected $dm;

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
     * @since 0.0.6 hold translatable settings
     * @var array
     */
    protected $i18nSettings = array();

    /**
     * @var array of Settings
     */
    protected $settingDocuments = array();

    protected $basePath = '/cms/settings';

    protected $locale;

    public function __construct(ManagerRegistry $mr, array $resourcePaths = array())
    {
        $this->dm      = $mr->getManager(); // document manager
        $this->session = $mr->getConnection();

        $this->loadDefaultSettings($resourcePaths);
        $this->loadSettings();
    }

    /**
     * {@inheritDoc}
     */
    public function setLocale($locale)
    {
        if ($this->locale === $locale) {
            return;
        }

        $this->locale = $locale;

        // we need to reload all i18nSettings due to locale changes
        $this->loadSettings(I18nSetting::class);
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

        // setting is an array of values, no need to further process
        if (is_array($setting)) {
            return $setting;
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
            $value = $this->normalizeValue($key, $value);

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
        $groupPath   = $this->basePath . '/' . $groupName;
        $newSettings = $this->flatten($newSettings, $groupName);

        if (!$rootGroup = $this->dm->find(null, $groupPath)) {
            throw new \DomainException(sprintf('Invalid setting group named "%s". Make sure you have initialized the group in database.', $groupName));
        }

        // need to unNormalize all setting values
        array_walk($newSettings, function(&$value, $name) {
            $value = $this->unNormalizeValue($name, $value);
        });

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
            $settingName = preg_replace('#^' . $this->basePath . '/#', '', $dbSetting->getId());
            $settingName = str_replace('/', '.', $settingName);

            if (array_key_exists($settingName, $newSettings)) {
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

            $settingClass = array_key_exists($name, $this->i18nSettings)
                ? I18nSetting::class : Setting::class;

            $dbSetting = new $settingClass($settingName, $newSetting);
            $dbSetting->setParentDocument($parentDocument);

            $this->dm->persist($dbSetting);
        }

        $this->dm->flush();
    }

    /**
     * Normalize a setting's value
     *
     * Settings' values are stored as string, therefore we need to normalize
     * them before they can be used with other parts of the system, such as
     * Symfony's Form Component
     *
     * @param string $name
     * @param string $value
     */
    protected function normalizeValue($name, $value)
    {
        // if this is a boolean value
        if (strpos($name, 'enable_') !== false) {
            return $value == 'yes' ? true : false;
        }

        return $value;
    }

    /**
     * Convert a setting's value back to its original one
     *
     * @param string $name
     * @param string $value
     */
    protected function unNormalizeValue($name, $value)
    {
        // if this is a boolean value
        if (strpos($name, 'enable_') !== false) {
            return $value === true ? 'yes' : '';
        }

        return $value;
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

            /* @var $file SplFileInfo */
            foreach ($settingFiles as $file) {
                $settings = include $file;
                if (!is_array($settings)) {
                    throw new \InvalidArgumentException('Provided default settings resource is invalid, please use a PHP file that returns an array.');
                }

                $settingPrefix = str_replace(array('_i18n.php', '.php'), '', $file->getFileName());
                $flattenedSettings = $this->flatten($settings, $settingPrefix);

                $this->defaultSettings = array_merge($this->defaultSettings, $flattenedSettings);

                // load into i18nSettings as well, if applicable
                if (preg_match('/_i18n\.php$/i', $file->getFilename())) {
                    $this->i18nSettings = array_merge($this->i18nSettings, $flattenedSettings);
                }
            }
        }

        $this->settings = $this->defaultSettings;
    }

    /**
     * Load settings that are marked as 'autoload' from database and merge
     * them with default settings
     *
     * Since we're doing a reload, there's no reason to use a reslt cache
     *
     * @param string $settingClass
     */
    protected function loadSettings($settingClass = null)
    {
        $settingClass = $settingClass ?: Setting::class;
        $settings     = $this->dm->getRepository($settingClass)->findBy(array('autoload' => true));

        /* @var $setting Setting */
        foreach ($settings as $setting) {
            $settingName = preg_replace('#^' . $this->basePath . '/#', '', $setting->getId(), 1);
            $settingName = str_replace('/', '.', $settingName);

            if ($setting instanceof I18nSetting && $this->locale) {
                if ($this->locale !== $setting->getLocale()) {
                    $setting = $this->dm->findTranslation($settingClass, $setting->getId(), $this->locale);
                }
            }

            // a nulll value means the field value isn't available
            if (!is_null($setting->getValue())) {
                $this->settings[$settingName] = $setting->getValue();
            }

            $this->settingDocuments[$settingName] = $setting;
        }
    }
}
