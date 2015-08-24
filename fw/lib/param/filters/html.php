<?php

namespace Difra\Param\Filters;

use Difra\Envi;
use Difra\Libs\ESAPI;
use Difra\Libs\XML\DOM;

class HTML
{
    /** @var array Allowed tag=>parameter=>filterMethod */
    private $allowedTags = [
        'a'      => ['href' => 'cleanLink'],
        'img'    => ['src' => 'cleanLink'],
        'br'     => [],
        'table'  => [],
        'tr'     => [],
        'td'     => ['colspan' => 'cleanUnsignedInt', 'rowspan' => 'cleanUnsignedInt'],
        'div'    => [],
        'em'     => [],
        'li'     => [],
        'ol'     => [],
        'p'      => [],
        'span'   => [],
        'strike' => [],
        'u'      => [],
        'ul'     => [],
        'strong' => [],
        'sub'    => [],
        'sup'    => [],
        'hr'     => []
    ];
    /** @var array Parameters allowed for all tags, parameter=>filterMethod */
    private $allowedAttrsForAll = [
        'style' => 'cleanStyles',
        'class' => 'cleanClasses'
    ];
    /** @var array Allowed styles list. Array lists values, true allows any value. */
    private $allowedStyles = [
        'font-weight'     => [
            'bold',
            'bolder',
            'lighter',
            'normal',
            '100',
            '200',
            '300',
            '400',
            '500',
            '600',
            '700',
            '800',
            '900'
        ],
        'text-align'      => ['left', 'center', 'right', 'start', 'end'],
        'color'           => true,
        'text-decoration' => ['line-through', 'overline', 'underline', 'none'],
        'font-style'      => ['normal', 'italic', 'oblique']
    ];

    /**
     * Singleton
     *
     * @return self
     */
    static public function getInstance()
    {
        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * HTML processor
     *
     * @param string $source      Source HTML
     * @param bool   $clean       Perform cleaning
     * @param bool   $withHeaders Return full HTML page (true) or contents only (false)
     * @return string
     */
    public function process($source, $clean = true, $withHeaders = false)
    {
        if (!trim($source)) {
            return '';
        }

        /*try {
            $source = \Difra\Libs\ESAPI::encoder()->canonicalize( $source );
        } catch( \Exception $ex ) {
            return false;
        }*/

        // html to dom conversion
        $html = new \DOMDocument('1.0');
        libxml_use_internal_errors(true);
        $html->loadHTML('<?xml version = "1.0" encoding = "utf-8"?>' . $source);
        libxml_use_internal_errors(false);
        $html->normalize();

        // clean dom
        if ($clean) {
            $bodyList = $html->documentElement->getElementsByTagName('body');
            if ($bodyList->length and $bodyList->item(0)->childNodes->length) {
                $body = $bodyList->item(0);
                $replaceNodes = [];
                foreach ($body->childNodes as $node) {
                    $newReplaceNodes = $this->clean($node);
                    $replaceNodes = array_merge($replaceNodes, $newReplaceNodes);
                }
                if (!empty($replaceNodes)) {
                    foreach ($replaceNodes as $replaceNode) {
                        $this->replace($replaceNode);
                    }
                }
            } else {
                return false;
            }
        }

        // dom to html conversion
        if ($withHeaders) {
            $output = $html->saveHTML();
        } else {
            $newDom = new \DOMDocument();
            foreach ($html->documentElement->childNodes as $node) {
                if ($node->nodeName == 'body') {
                    foreach ($node->childNodes as $subNode) {
                        $newDom->appendChild($newDom->importNode($subNode, true));
                    }
                }
            }
            $output = $newDom->saveHTML();
            // TODO: convert xhtml to html5
        }
        return mb_convert_encoding($output, 'UTF-8', 'HTML-ENTITIES');
    }

    /**
     * Clean everything but allowed elements
     *
     * @param \DOMElement|\DOMNode $node
     * @return \DOMElement[]
     */
    private function clean(&$node)
    {
        $replaceNodes = [];
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                if (!isset($this->allowedTags[$node->nodeName])) {
                    $replaceNodes[] = $node;
                }
                $this->cleanAttributes($node,
                                       isset($this->allowedTags[$node->nodeName]) ? $this->allowedTags[$node->nodeName]
                                           : []
                );
                break;
            case XML_TEXT_NODE:
                break;
            case XML_COMMENT_NODE:
                $replaceNodes[] = $node;
                break;
            default:
                $replaceNodes[] = $node;
        }
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $newReplace = $this->clean($child);
                $replaceNodes = array_merge($newReplace, $replaceNodes);
            }
        }
        return $replaceNodes;
    }

    /**
     * Clean all atttributes but allowed
     *
     * @param \DOMElement|\DOMNode $node
     * @param array                $attributes
     */
    private function cleanAttributes(&$node, $attributes = [])
    {
        if (($node instanceof \DOMElement or $node instanceof \DOMNode) and $node->attributes->length) {
            $delAttr = [];
            foreach ($node->attributes as $attr) {
                if (isset($attributes[$attr->name])) {
                    $filter = $attributes[$attr->name];
                    $node->setAttribute($attr->name, $this->$filter($attr->value));
                } elseif (isset($this->allowedAttrsForAll[$attr->name])) {
                    $filter = $this->allowedAttrsForAll[$attr->name];
                    $node->setAttribute($attr->name, $this->$filter($attr->value));
                } else {
                    $delAttr[] = $attr->name;
                }
            }
            foreach ($delAttr as $da) {
                $node->removeAttribute($da);
            }
        }
    }

    /**
     * If some disallowed element is not empty, replace it with span
     *
     * @param \DOMElement $node
     */
    private function replace(&$node)
    {
        if (!$node->hasChildNodes()) {
            $node->parentNode->removeChild($node);
            return;
        }
        DOM::renameNode($node, 'span');
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Links filter
     *
     * @param string $link
     * @return string
     */
    private function cleanLink($link)
    {
        if (ESAPI::validateURL($link)) {
            return ESAPI::encoder()->encodeForHTMLAttribute($link);
        }
        if (mb_substr($link, 0, 1) == '/') {
            $newLink = 'http://' . Envi::getHost() . $link;
            if (ESAPI::validateURL($newLink)) {
                return ESAPI::encoder()->encodeForHTMLAttribute($newLink);
            }
        }
        return '#';
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Styles filter
     *
     * @param string $attr
     * @return string
     */
    private function cleanStyles($attr)
    {
        $returnStyle = [];
        $stylesSet = explode(';', $attr);
        foreach ($stylesSet as $value) {
            $styleElements = explode(':', $value, 2);
            if (sizeof($styleElements) != 2) {
                continue;
            }

            $styleElements[0] = trim($styleElements[0]);
            $styleElements[1] = trim($styleElements[1]);

            // проверяем элемент
            if (array_key_exists($styleElements[0], $this->allowedStyles)) {

                // проверяем значение
                if ($this->allowedStyles[$styleElements[0]] === true) {
                    $returnStyle[] =
                        $styleElements[0] . ': ' . ESAPI::encoder()->encodeForCSS($styleElements[1]);
                } elseif (is_array($this->allowedStyles[$styleElements[0]])
                          and in_array($styleElements[1], $this->allowedStyles[$styleElements[0]])
                ) {
                    $returnStyle[] = $styleElements[0] . ':' . $styleElements[1];
                }
            }
        }
        return implode(';', $returnStyle);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Classes filter
     *
     * @param string $classes
     * @return string
     */
    private function cleanClasses($classes)
    {
        $newClasses = [];
        $cls = explode(' ', $classes);
        foreach ($cls as $cl) {
            // Classes named st-* are used for predefined WYSIWYG editor styles
            if ((mb_substr($cl, 0, 3) == 'st-') and ctype_alnum(mb_substr($cl, 3))) {
                $newClasses[] = $cl;
            }
        }
        return implode(' ', $newClasses);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    /**
     * Positive integers filter
     *
     * @param $input
     * @return int|string
     */
    private function cleanUnsignedInt($input)
    {
        $input = intval($input);
        if (filter_var($input, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            return $input;
        } else {
            return '';
        }
    }
}
