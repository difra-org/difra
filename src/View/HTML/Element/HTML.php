<?php

namespace Difra\View\HTML\Element;

use Difra\View\HTML\Element;

class HTML extends \Difra\View\HTML\Element
{
    protected static $unique = true;

    /** @var Element */
    protected $head = null;
    /** @var Element */
    protected $body = null;

    public function getHead(): Head
    {
        if (!$this->head) {
            $this->head = new Head();
            $this->head->setParent($this);
            $this->children[] =& $this->head;
        }
        return $this->head;
    }

    public function getBody(): Body
    {
        if (!$this->body) {
            $this->body = new Body();
            $this->body->setParent($this);
            $this->children[] =& $this->body;
        }
        return $this->body;
    }
}