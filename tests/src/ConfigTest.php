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
}