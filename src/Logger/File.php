<?php

namespace Difra\Logger;

/**
 * Class File
 * @package Difra\Logger
 */
class File extends Common
{
    /** @var string Log file name */
    protected $file = null;

    /**
     * @inheritdoc
     */
    protected function realWrite($message, $level)
    {
        file_put_contents($this->config['file'], $this->format($message) . "\n", FILE_APPEND | LOCK_EX);
    }
}
