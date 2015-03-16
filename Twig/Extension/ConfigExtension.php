<?php

/**
 * This file is part of the Kdm package.
 *
 * (c) 2015 Khang Minh <kminh@kdmlabs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kdm\ConfigBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;

use Kdm\ConfigBundle\Model\SettingManagerInterface;

/**
 * @author Khang Minh <kminh@kdmlabs.com>
 */
class ConfigExtension extends \Twig_Extension
{
    protected $settings;

    protected $kernel;

    public function __construct(KernelInterface $kernel, SettingManagerInterface $settings)
    {
        $this->kernel   = $kernel;
        $this->settings = $settings;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('setting', array($this, 'getSetting'))
        );
    }

    public function getSetting($settingName)
    {
        try {
            return $this->settings->get($settingName);
        } catch (\Exception $e) {
            // throw error if setting is not found in environments that are not prod
            if ($this->kernel->getEnvironment() != 'prod') {
                throw $e;
            }
        }
    }

    public function getName()
    {
        return 'kdm_config_extension';
    }
}
