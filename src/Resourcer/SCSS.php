<?php

namespace Difra\Resourcer;

class SCSS extends Abstracts\Plain
{
    protected $type = 'scss';
    protected $printable = true;
    protected $contentType = 'text/css';
    protected $instancesOrdered = true;

    protected function __construct()
    {
        if (\Difra\Debugger::isEnabled()) {
            $this->printSequenceDebug = true;
        }
    }

    protected function getFile($file)
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
