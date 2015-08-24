<?php

namespace Difra\Resourcer;

use Difra\Debugger;

/**
 * Class Menu
 *
 * @package Difra\Resourcer
 */
class Menu extends Abstracts\XML
{
    protected $type = 'menu';
    protected $printable = false;

    /**
     * @param \SimpleXMLElement $xml
     * @param string            $instance
     */
    protected function postprocess($xml, $instance)
    {
        $xml->addAttribute('instance', $instance);
        /** @noinspection PhpUndefinedFieldInspection */
        if ($xml->attributes()->prefix) {
            /** @noinspection PhpUndefinedFieldInspection */
            $prefix = $xml->attributes()->prefix;
        } else {
            $prefix = '/' . $instance;
        }
        $this->_recursiveProcessor($xml, $prefix, 'menu', $instance);
    }

    /**
     * @param \SimpleXMLElement $node
     * @param string            $href
     * @param string            $prefix
     * @param string            $instance
     * @internal param string $url
     */
    private function _recursiveProcessor($node, $href, $prefix, $instance)
    {
        /** @var \SimpleXMLElement $subNode */
        foreach ($node as $subname => $subNode) {
            /** @noinspection PhpUndefinedFieldInspection */
            if ($subNode->attributes()->sup and $subNode->attributes()->sup == '1') {
                if (!Debugger::isEnabled()) {
                    $subNode->addAttribute('hidden', 1);
                }
            }
            $newHref = "$href/$subname";
            $newPrefix = "{$prefix}_{$subname}";
            $subNode->addAttribute('id', $newPrefix);
            /** @noinspection PhpUndefinedFieldInspection */
            if (!isset($subNode->attributes()->href)) {
                $subNode->addAttribute('href', $newHref);
            };
            $subNode->addAttribute('pseudoHref', $newHref);
            $subNode->addAttribute('xpath', 'locale/menu/' . $instance . '/' . $newPrefix);
            $this->_recursiveProcessor($subNode, $newHref, $newPrefix, $instance);
        }
    }
}
