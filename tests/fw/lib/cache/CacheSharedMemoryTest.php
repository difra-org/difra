<?php

include_once(__DIR__ . '/../CacheTest.inc');

class CacheSharedMemoryTest extends CacheTest
{
    static $inst = \Difra\Cache::INST_SHAREDMEM;
}
