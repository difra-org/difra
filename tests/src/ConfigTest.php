<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testLoad()
    {
        $config = \Difra\Config::getInstance();
        $value = $config->get('test');
        $this->assertNull($value);
    }

    public function testSet()
    {
        $config = \Difra\Config::getInstance();
        $config->set('testField', 'testValue');
        $this->assertEquals('testValue', $config->get('testField'));
    }

    public function testSetValueFail()
    {
        $config = \Difra\Config::getInstance();
        $config->set('testField', 'testValue');
        $this->expectException('Difra\Exception');
        $config->setValue('testField', 'testSubField', 'testValue');
    }

    public function testSetValue1()
    {
        $config = \Difra\Config::getInstance();
        $config->set('testField', ['subField2' => 'subValue2']);
        $config->setValue('testField', 'testSubField', 'testValue');
        $this->assertEquals('testValue', $config->getValue('testField', 'testSubField'));
        $this->assertEquals('subValue2', $config->getValue('testField', 'subField2'));
    }

    public function testSetValue2()
    {
        $config = \Difra\Config::getInstance();
        $config->setValue('testField2', 'testSubField', 'testValue');
        $this->assertEquals('testValue', $config->getValue('testField2', 'testSubField'));
    }

    public function testGetAll()
    {
        $config = \Difra\Config::getInstance()->getConfig();
        $this->assertIsArray($config);
        $this->assertTrue(!empty($config['testField2']));
    }
}