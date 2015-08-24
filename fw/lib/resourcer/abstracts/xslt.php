<?php

namespace Difra\Resourcer\Abstracts;

/**
 * Class XSLT
 *
 * @package Difra\Resourcer\Abstracts
 */
abstract class XSLT extends Common
{
	protected function processData($instance)
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
		foreach ($files as $filename) {
			$template = new \DOMDocument();
			$template->load($filename['raw']);
			/** @var \DOMElement $child */
			foreach ($template->documentElement->childNodes as $child) {
				switch ($child->nodeType) {
					case XML_TEXT_NODE:
					case XML_COMMENT_NODE:
						continue 2;
				}
				if ($child->nodeName == 'xsl:template') {
					if ($child->hasAttribute('match')) {
						$m = $child->getAttribute('match');
						if ($child->hasAttribute('mode')) {
							$m .= ':' . $child->getAttribute('mode');
						}
						if (in_array($m, $usedMatches)) {
							continue;
						}
						$usedMatches[] = $m;
					} elseif ($child->hasAttribute('name')) {
						$n = $child->getAttribute('name');
						if ($child->hasAttribute('mode')) {
							$n .= ':' . $child->getAttribute('mode');
						}
						if (in_array($n, $usedNames)) {
							continue;
						}
						$usedNames[] = $n;
					} else {
						continue;
					}
				} elseif ($child->nodeName == 'xsl:output') {
					continue;
				}
				$dom->documentElement->appendChild($dom->importNode($child, true));
			}
		}
		return $dom->saveXML();
	}
}
