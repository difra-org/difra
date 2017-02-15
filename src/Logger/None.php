<?php

namespace Difra\Logger;

/**
 * Class None
 * @package Difra\Logger
 */
class None extends Common {
    /**
     * @inheritdoc
     */
    protected function realWrite($message, $level)
    {
    }
}
