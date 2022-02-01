<?php

declare(strict_types=1);

namespace Difra\Param\Filters;

use Difra\Libs\XML\DOM;

/**
 * Class HTML
 * HTML filter.
 * @package Difra\Param\Filters
 */
class HTML
{
    /** @var array Allowed tag=>parameter=>filterMethod */
    private array $allowedTags = [
        'a' => ['href' => 'cleanLink'],
        'img' => ['src' => 'cleanLink'],
        'br' => [],
        'table' => [],
        'tr' => [],
        'td' => ['colspan' => 'cleanUnsignedInt', 'rowspan' => 'cleanUnsignedInt'],
        'div' => [],
        'em' => [],
        'li' => [],
        'mark' => [],
        'ol' => [],
        'p' => [],
        'span' => [],
        'strike' => [],
        'u' => [],
        'ul' => [],
        'strong' => [],
        'sub' => [],
        'sup' => [],
        'hr' => []
    ];
    /** @var array Parameters allowed for all tags, parameter=>filterMethod */
    private array $allowedAttrsForAll = [
        'style' => 'cleanStyles',
        'class' => 'cleanClasses'
    ];
    /** @var array Allowed styles list. Array lists values, true allows any value. */
    private array $allowedStyles = [
        'font-weight' => [
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
        'text-align' => ['left', 'center', 'right', 'start', 'end'],
        'color' => true,
        'text-decoration' => ['line-through', 'overline', 'underline', 'none'],
        'font-style' => ['normal', 'italic', 'oblique']
    ];

    /**
     * Singleton
     * @return self
     */
    public static function getInstance(): HTML
    {
        static $instance = null;
        return $instance ?? $instance = new self();
    }

    /**
     * HTML processor
     * @param string $source Source HTML
     * @param bool $clean Perform cleaning
     * @param bool $withHeaders Return full HTML page (true) or contents only (false)
     * @return string|null
     */
    public function process(string $source, bool $clean = true, bool $withHeaders = false): ?string
    {
        if (!trim($source)) {
            return '';
        }

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
                return null;
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
            // TODO: convert xhtml to html5?
        }
        return mb_convert_encoding($output, 'UTF-8', 'HTML-ENTITIES');
    }

    /**
     * Clean everything but allowed elements
     * @param \DOMElement $node
     * @return \DOMElement[]
     */
    private function clean(\DOMElement &$node): array
    {
        $replaceNodes = [];
        switch ($node->nodeType) {
            case XML_ELEMENT_NODE:
                if (!isset($this->allowedTags[$node->nodeName])) {
                    $replaceNodes[] = $node;
                }
                $this->cleanAttributes(
                    $node,
                    $this->allowedTags[$node->nodeName] ?? []
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
     * Clean all attributes but allowed
     * @param \DOMElement $node
     * @param array $attributes
     */
    private function cleanAttributes(\DOMElement $node, array $attributes = [])
    {
        if ($node->attributes->length) {
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
     * @param \DOMElement $node
     */
    private function replace(\DOMElement $node)
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
     * @param string $link
     * @return string
     */
    private function cleanLink(string $link): string
    {
        // TODO
        trigger_error('New HTML::cleanLink() needs to be written, please do not rely on it.', E_USER_WARNING);
        return $link;
    }

    /**
     * Encode string to get a proper CSS parameter value
     * @param string $str
     * @return string
     */
    private function encodeForCSS(string $str):string
    {
        // TODO
        trigger_error('New HTML::encodeForCSS() needs to be written, please do not rely on it.', E_USER_WARNING);
        return $str;
    }

    /**
     * Styles filter
     * @param string $attr
     * @return string
     */
    private function cleanStyles(string $attr): string
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

            // verify element
            if (array_key_exists($styleElements[0], $this->allowedStyles)) {
                // verify value
                if ($this->allowedStyles[$styleElements[0]] === true) {
                    $returnStyle[] =
                        $styleElements[0] . ': ' . self::encodeForCSS($styleElements[1]);
                } elseif (is_array($this->allowedStyles[$styleElements[0]])
                          and in_array($styleElements[1], $this->allowedStyles[$styleElements[0]])
                ) {
                    $returnStyle[] = $styleElements[0] . ':' . $styleElements[1];
                }
            }
        }
        return implode(';', $returnStyle);
    }

    /**
     * Classes filter
     * @param string $classes
     * @return string
     */
    private function cleanClasses(string $classes): string
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

    /**
     * Positive integers filter
     * @param $input
     * @return string|null
     */
    private function cleanUnsignedInt($input): ?string
    {
        $input = intval($input);
        if (filter_var($input, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            return (string)$input;
        } else {
            return null;
        }
    }
}
