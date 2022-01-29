<?php

declare(strict_types=1);

namespace Difra\Resourcer;

use Difra\Debugger;

/**
 * Class Menu
 * @package Difra\Resourcer
 */
class Menu extends Abstracts\XML
{
    /** @var string|null Menu resourcer */
    protected ?string $type = 'menu';
    /** @var bool Don't view resource */
    protected bool $printable = false;

    /**
     * @param \SimpleXMLElement $xml
     * @param string $instance
     */
    protected function postprocess(\SimpleXMLElement $xml, string $instance)
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
    private function recursiveProcessor(\SimpleXMLElement $node, string $href, string $prefix, string $instance)
    {
        /** @var \SimpleXMLElement $subNode */
        foreach ($node as $subName => $subNode) {
            if ($subNode->attributes()->sup and $subNode->attributes()->sup == '1') {
                if (!Debugger::isEnabled()) {
                    $subNode->addAttribute('hidden', 1);
                }
            }
            $newHref = "$href/$subName";
            $newPrefix = "{$prefix}_$subName";
            $subNode->addAttribute('id', $newPrefix);
            if (!isset($subNode->attributes()->href)) {
                $subNode->addAttribute('href', $newHref);
            }
            $subNode->addAttribute('pseudoHref', $newHref);
            $subNode->addAttribute('xpath', 'locale/menu/' . $instance . '/' . $newPrefix);
            $this->recursiveProcessor($subNode, $newHref, $newPrefix, $instance);
        }
    }
}
