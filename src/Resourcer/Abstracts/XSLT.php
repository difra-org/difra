<?php

declare(strict_types=1);

namespace Difra\Resourcer\Abstracts;

/**
 * Class XSLT
 * @package Difra\Resourcer\Abstracts
 */
abstract class XSLT extends Common
{
    protected bool $reverseIncludes = false;
    
    /**
     * @inheritdoc
     */
    protected function processData(string $instance): string|bool
    {
        /*
        <!DOCTYPE xsl:stylesheet [
        <!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-lat1.ent">
        <!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-symbol.ent">
        <!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-special.ent">
        %lat1;
        %symbol;
        %special;
        ]>
        */

        $files = $this->getFiles($instance);
        $template = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>
				<!DOCTYPE xsl:stylesheet>
				<xsl:stylesheet
					version="1.0"
					xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
					xmlns="http://www.w3.org/1999/xhtml">

					<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes"/>
					<xsl:param name="locale" select="/root/locale"/>
					<xsl:template match="/root/locale"/>
				</xsl:stylesheet>';
        $dom = new \DOMDocument();
        $dom->loadXML($template);
        $usedNames = [];
        $usedMatches = [];
        $namespaces = [];
        foreach ($files as $filename) {
            $template = new \DOMDocument();
            $template->load($filename['raw']);
            $xpath = new \DOMXPath($template);
            foreach ($xpath->query('namespace::*', $template->documentElement) as $nsNode) {
                $namespaces[$nsNode->nodeName] = $nsNode->nodeValue;
            }
            /** @var \DOMElement $child */
            foreach ($template->documentElement->childNodes as $child) {
                switch ($child->nodeType) {
                    case XML_TEXT_NODE:
                    case XML_COMMENT_NODE:
                        continue 2;
                }
                if ($child->nodeName == 'xsl:template') {
                    if ($child->hasAttribute('match')) {
                        $match = $child->getAttribute('match');
                        if ($child->hasAttribute('mode')) {
                            $match .= ':' . $child->getAttribute('mode');
                        }
                        if (in_array($match, $usedMatches)) {
                            continue;
                        }
                        $usedMatches[] = $match;
                    } elseif ($child->hasAttribute('name')) {
                        $name = $child->getAttribute('name');
                        if ($child->hasAttribute('mode')) {
                            $name .= ':' . $child->getAttribute('mode');
                        }
                        if (in_array($name, $usedNames)) {
                            continue;
                        }
                        $usedNames[] = $name;
                    } else {
                        continue;
                    }
                } elseif ($child->nodeName == 'xsl:output') {
                    continue;
                }
                $dom->documentElement->appendChild($dom->importNode($child, true));
            }
        }
        if (!empty($namespaces)) {
            foreach ($namespaces as $key => $value) {
                $dom->documentElement->setAttribute($key, $value);
            }
        }

        return $dom->saveXML();
    }
}
