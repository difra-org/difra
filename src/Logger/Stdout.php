<?php

namespace Difra\Logger;

/**
 * Class Stdout
 * @package Drafton\Logger
 */
class Stdout extends Common
{
    /**
     * @inheritdoc
     */
    protected function realWrite($message, $level)
    {
        echo $this->format($message), PHP_EOL;
    }
}
