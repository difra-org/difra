<?php

declare(strict_types=1);

namespace Tests;
use Difra\Envi;
use PHPUnit\Framework\TestCase;

class EnviTest extends TestCase {
    public function testGetMode()
    {
        $this->assertEquals(Envi::MODE_CLI, Envi::getMode());
    }

    public function testSetMode()
    {
        Envi::setMode(Envi::MODE_WEB);
        $this->assertEquals(Envi::MODE_WEB, Envi::getMode());
        Envi::setMode(Envi::MODE_CLI);
        $this->assertEquals(Envi::MODE_CLI, Envi::getMode());
    }

    public function testGetUri()
    {
        $this->assertNull(Envi::getUri());
        $_SERVER['REQUEST_URI'] = '/test/page/123?param=1';
        $this->assertEquals('/test/page/123', Envi::getUri());
    }

    public function testSetUri()
    {
        Envi::setUri('/test/page/321');
        $this->assertEquals('/test/page/321', Envi::getUri());
    }

    public function testIsProduction()
    {
        $this->assertTrue(Envi::isProduction());
    }

    public function testGetHost()
    {
        $this->assertIsString(Envi::getHost());
        $_SERVER['HTTP_HOST'] = 'sub.example.com';
        $this->assertEquals('sub.example.com', Envi::getHost(false));
        $this->assertEquals('sub.example.com', Envi::getHost(true));
        $_SERVER['VHOST_MAIN'] = 'example.com';
        $this->assertEquals('sub.example.com', Envi::getHost(false));
        $this->assertEquals('example.com', Envi::getHost(true));
    }

    public function testGetProtocol()
    {
        $this->assertEquals('http', Envi::getProtocol());
        $_SERVER['HTTPS'] = 'on';
        $this->assertEquals('https', Envi::getProtocol());
    }

    public function testGetUrlPrefix()
    {
        $this->assertEquals('https://sub.example.com', Envi::getURLPrefix(false));
        $this->assertEquals('https://example.com', Envi::getURLPrefix(true));
    }

    public function testGetSubSite()
    {
        $this->assertEquals('default', Envi::getSubsite(false));
        $_SERVER['VHOST_NAME'] = 'test';
        $this->assertEquals('test', Envi::getSubsite(false));
        $_SERVER['VHOST_NAME'] = 'sub.example.com';
        $this->assertEquals('sub.example.com', Envi::getSubsite(false));
    }

    public function testGetState()
    {
        $this->assertEquals([
            'locale' => 'ru_RU',
            'host' => null,
            'hostname' => 'sub.example.com',
            'mainhost' => 'example.com',
            'fullhost' => 'https://sub.example.com',
            'build' => Envi\Version::getBuild(false),
            'buildShort' => Envi\Version::getBuild(false)
        ], Envi::getState());
    }
}