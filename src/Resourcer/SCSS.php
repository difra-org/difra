<?php

declare(strict_types=1);

namespace Difra\Resourcer;

/**
 * SCSS resourcer
 */
class SCSS extends Abstracts\Plain
{
    protected ?string $type = 'scss';
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
     * Choose most suitable file
     * @param array $file
     * @return string
     */
    protected function getFile(array $file): string
    {
        echo $file['raw'] . "\n";
        static $scssCompiler = null;
        if (!$scssCompiler) {
            $scssCompiler = new \ScssPhp\ScssPhp\Compiler();
            if (!\Difra\Debugger::isEnabled()) {
                $scssCompiler->setFormatter('\ScssPhp\ScssPhp\Formatter\Crunched');
            } else {
                $scssCompiler->setFormatter('\ScssPhp\ScssPhp\Formatter\Expanded');
            }
        }
        $prefix = \Difra\Debugger::isEnabled() ? "\n\n/* File: {$file['raw']} */\n\n" : '';
        return $prefix . $scssCompiler->compile(file_get_contents($file['raw']));
    }
}
