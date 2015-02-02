<?php

namespace Kdm\ConfigBundle\Tests\Entity;

use Kdm\ConfigBundle\Entity\Setting;

/**
 * @covers Kdm\Bundle\ConfigBundle\Entity\Setting
 * @author Khang Minh <kminh@kdm.com>
 */
class SettingTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    /**
     * @covers Setting::__constructor
     * @expectedException \Exception
     */
    public function testNewSettingWithoutNameAndValue()
    {
        $setting = new Setting();
    }

    /**
     * @covers Setting::__constructor
     * @expectedException \InvalidArgumentException
     */
    public function testNewSettingWithoutAValidName()
    {
        $setting = new Setting(null, 'test');
    }

    /**
     * @covers Setting::__constructor
     * @expectedException \Exception
     */
    public function testNewSettingWithoutValue()
    {
        $setting = new Setting('title');
    }

    /**
     * @covers Setting::__constructor
     */
    public function testNewSettingWithNameAndValue()
    {
        $setting = new Setting('title', 'website');

        $this->assertEquals('title', $setting->getName());
        $this->assertEquals('website', $setting->getValue());
    }

    /**
     * @covers Setting::setValue
     */
    public function testSetValue()
    {
        $setting = new Setting('title', 'website');

        $setting->setValue('another title');

        $this->assertEquals('another title', $setting->getValue());
    }

    /**
     * @covers Setting::setValue
     * @covers Setting::reload
     */
    public function testReloadAndClearReload()
    {
        $setting = new Setting('title', 'website');

        $setting->reload();
        $this->assertSame(true, $setting->needReload());

        // reload should be cleared when a new value is set
        $setting->setValue('another title');
        $this->assertEquals('another title', $setting->getValue());
        $this->assertSame(false, $setting->needReload());
    }
}
