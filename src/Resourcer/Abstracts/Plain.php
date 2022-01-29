<?php

declare(strict_types=1);

namespace Difra\Resourcer\Abstracts;

use Difra\Debugger;
use Difra\Minify;

/**
 * Abstract adapter for text resources
 */
abstract class Plain extends Common
{
    protected bool $showSequence = false;

    /**
     * Combine resources to single string
     * @param string $instance
     * @return string
     */
    protected function processData(string $instance): string
    {
        if ($this->showSequence) {
            echo("/*\n\nIncluded files order:\n\n");
        }
        $result = '';
        if (!$this->instancesOrdered) {
            if (!empty($this->resources[$instance]['specials'])) {
                foreach ($this->resources[$instance]['specials'] as $resource) {
                    if (!empty($resource['files'])) {
                        foreach ($resource['files'] as $file) {
                            $result .= $this->getFile($file);
                        }
                    }
                }
            }
        }
        if (!empty($this->resources[$instance]['files'])) {
//            $this->resources[$instance]['files'] = array_reverse($this->resources[$instance]['files']);
            foreach ($this->resources[$instance]['files'] as $file) {
                $result .= $this->getFile($file);
            }
        }
        if ($this->instancesOrdered) {
            if (!empty($this->resources[$instance]['specials'])) {
                foreach ($this->resources[$instance]['specials'] as $resource) {
                    if (!empty($resource['files'])) {
                        foreach ($resource['files'] as $file) {
                            $result .= $this->getFile($file);
                        }
                    }
                }
            }
        }
        if ($this->showSequence) {
            echo("\n*/\n\n");
        }
        return $result;
    }

    /**
     * Choose most suitable file
     * @param array $file
     * @return string
     */
    protected function getFile(array $file): string
    {
        $debuggerEnabled = Debugger::isEnabled();
        if (!$debuggerEnabled) {
            if (!empty($file['min'])) {
                return file_get_contents($file['raw']);
            } elseif (!empty($file['raw'])) {
                return Minify::getInstance($this->type)->minify(file_get_contents($file['raw']));
            } else {
                return '';
            }
        }
        if (!empty($file['raw'])) {
            $selectedVersion = 'raw';
        } elseif (!empty($file['min'])) {
            $selectedVersion = 'min';
        } else {
            echo("Resource file search problem\n");
            return '';
        }
        if (!$this->showSequence) {
            return file_get_contents($file[$selectedVersion]);
        }
        echo($file[$selectedVersion] . "\n");
        return "\n\n/* File: $file[$selectedVersion] */\n\n" . file_get_contents($file[$selectedVersion]);
    }
}
