<?php

declare(strict_types=1);

namespace Difra\Resourcer;

/**
 * Class CSS
 * @package Difra\Resourcer
 */
class CSS extends Abstracts\Plain
{
    protected ?string $type = 'css';
    protected bool $printable = true;
    protected ?string $contentType = 'text/css';
    protected bool $instancesOrdered = true;

    protected function __construct()
    {
        parent::__construct();
        if (\Difra\Debugger::isEnabled()) {
            $this->showSequence = true;
        }
    }

    /**
     * Resource postprocessing
     * @param string $text
     * @return string
     */
    public function processText(string $text): string
    {
        return \Difra\Libs\Less::compile($text);
    }
}
