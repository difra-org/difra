<?php

namespace Difra\View\HTML;

use Difra\View\HTML\Element\Link;
use Difra\View\HTML\Element\Script;

class Unique extends Element
{
    /** @var bool */
    protected static $unique = false;

    /**
     * Add script link
     * @param string $src
     * @param bool $async
     * @return static
     */
    public function addScript(string $src, bool $async = false, string $type = 'text/javascript'): self
    {
        $script = new Script();
        $script->setAttribute('src', $src);
        if ($async) {
            $script->setAttribute('async', 'async');
        }
        $this->addChild($script);
        return $this;
    }

    /**
     * Add stylesheet
     * @param string $href
     * @return static
     */
    public function addStylesheet(string $href): self
    {
        $link = new Link();
        $link->setAttribute('rel', 'stylesheet');
        $link->setAttribute('href', $href);
        $this->children[] = $link;
        return $this;
    }
}