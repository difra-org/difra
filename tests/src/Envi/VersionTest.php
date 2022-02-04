<?php

declare(strict_types=1);

namespace Tests\Envi;

use Difra\Envi\Version;
use PHPUnit\Framework\TestCase;

class VersionTest extends TestCase
{
    public function testGetBuild()
    {
        $this->assertNotNull($v1 = Version::getBuild(true));
        $this->assertNotNull($v2 = Version::getBuild(false));
        $this->assertEquals($v1, $v2);
        $this->assertTrue((bool) strpos($v1, '.'));
    }

    public function testGetFrameworkVersion()
    {
        $this->assertNotNull($v1 = Version::getFrameworkVersion());
        $this->assertTrue((bool) strpos($v1, '.'));
    }

    public function testGetMajorVersion()
    {
        $this->assertNotNull($v1 = Version::getMajorVersion());
        $this->assertTrue($v1 >= 8);
    }
}