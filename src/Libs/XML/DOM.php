<?php

namespace Difra\Libs\XML;

/**
 * Class DOM
 * @deprecated
 *          TODO: maybe clean it and remove @deprecated
 * @package Difra\Libs
 */
class DOM
{
    /**
     * for array2xml
     * @deprecated
     * @static
     * @return DOM
     */
    public static function getInstance()
    {
        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * Rename DOMNode
     * @param \DOMNode $node
     * @param string $newName
     */
    public static function renameNode($node, $newName)
    {
        $newNode = $node->ownerDocument->createElement($newName);
        if ($node->hasAttributes()) {
            foreach ($node->attributes as $attribute) {
                $newNode->setAttribute($attribute->nodeName, $attribute->nodeValue);
            }
        }
        while ($node->firstChild) {
            $newNode->appendChild($node->firstChild);
        }
        $node->parentNode->replaceChild($newNode, $node);
    }

    /**
     * Create DOM tree from array
     * @param \DOMNode $node
     * @param array $array
     */
    public static function array2dom(&$node, &$array)
    {
        if (is_array($array) and !empty($array)) {
            foreach ($array as $k => $v) {
                if (!is_array($v)) {
                    $node->appendChild($node->ownerDocument->createElement($k, $v));
                } else {
                    $subNode = $node->appendChild($node->ownerDocument->createElement($k));
                    self::array2dom($subNode, $v);
                }
            }
        }
    }

    /**
     * Add dom attributes from array
     * @static
     * @param \DOMElement|\DOMNode $node
     * @param array $array
     * @param bool $verbal
     */
    public static function array2domAttr(&$node, $array, $verbal = false)
    {
        if (is_array($array) and !empty($array)) {
            foreach ($array as $k => $v) {
                if (!$v) {
                } elseif (is_numeric($k) and !is_array($v) and !is_object($v) and ctype_alnum($v)) {
                    $node->appendChild($node->ownerDocument->createElement(ctype_alpha($v[0]) ? $v : "_$v"));
                } elseif (is_array($v)) {
                    if (is_numeric($k)) {
                        $k = "_$k";
                    }
                    $newNode = $node->appendChild($node->ownerDocument->createElement($k));
                    self::array2domAttr($newNode, $v, $verbal);
                } elseif (is_object($v)) {
                } else {
                    if ($verbal) {
                        if (is_null($v)) {
                            $v = 'null';
                        } elseif ($v === false) {
                            $v = 'false';
                        } elseif ($v === true) {
                            $v = 'true';
                        } elseif ($v === 0) {
                            $v = '0';
                        }
                    }
                    $node->setAttribute(ctype_alpha($k[0]) ? $k : "_$k", $v ?? '');
                }
            }
        }
    }
}
