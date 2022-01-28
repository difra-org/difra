<?php

namespace Difra\Resourcer;

use Difra\Debugger;

/**
 * Class Menu
 * @package Difra\Resourcer
 */
class Menu extends Abstracts\XML
{
    /** @var string Menu resourcer */
    protected $type = 'menu';
    /** @var bool Don't view resource */
    protected $printable = false;

    /**
     * @param \SimpleXMLElement $xml
     * @param string $instance
     */
    protected function postprocess($xml, $instance)
    {
        $xml->addAttribute('instance', $instance);
        if ($xml->attributes()->prefix) {
            $prefix = $xml->attributes()->prefix;
        } else {
            $prefix = '/' . $instance;
        }
        $this->recursiveProcessor($xml, $prefix, 'menu', $instance);
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string $href
     * @param string $prefix
     * @param string $instance
     * @internal param string $url
     */
    private function recursiveProcessor($node, $href, $prefix, $instance)
    {
        /** @var \SimpleXMLElement $subNode */
        foreach ($node as $subname => $subNode) {
            if ($subNode->attributes()->sup and $subNode->attributes()->sup == '1') {
                if (!Debugger::isEnabled()) {
                    $subNode->addAttribute('hidden', 1);
                }
            }
            $newHref = "$href/$subname";
            $newPrefix = "{$prefix}_{$subname}";
            $subNode->addAttribute('id', $newPrefix);
            if (!isset($subNode->attributes()->href)) {
                $subNode->addAttribute('href', $newHref);
            };
            $subNode->addAttribute('pseudoHref', $newHref);
            $subNode->addAttribute('xpath', 'locale/menu/' . $instance . '/' . $newPrefix);
            $this->recursiveProcessor($subNode, $newHref, $newPrefix, $instance);
        }
    }
}
