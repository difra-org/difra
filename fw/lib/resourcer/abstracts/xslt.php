<?php

namespace Difra\Resourcer\Abstracts;
use Difra;

abstract class XSLT extends Common {

	protected function processData( $instance ) {

		$files = $this->getFiles( $instance );
		$template = '<' . '?xml version="1.0" encoding="UTF-8"?' . '>
				<!DOCTYPE xsl:stylesheet [
					<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-lat1.ent">
					<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-symbol.ent">
					<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-special.ent">
					%lat1;
					%symbol;
					%special;
				]>
				<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
					<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes" indent="yes"/>
				</xsl:stylesheet>';
		$dom = new \DOMDocument();
		$dom->loadXML( $template );
		$usedNames = array();
		$usedMatches = array();
		foreach( $files as $filename ) {
			$template = new \DOMDocument();
			$template->load( $filename['raw'] );
			foreach( $template->documentElement->childNodes as $child ) {
				switch( $child->nodeType ) {
				case XML_TEXT_NODE:
				case XML_COMMENT_NODE:
					continue 2;
				}
				if( $child->nodeName == 'xsl:template' ) {
					if( $child->hasAttribute( 'match' ) ) {
						$m = $child->getAttribute( 'match' );
						if( $child->hasAttribute( 'mode' ) ) {
							$m .= ':' . $child->getAttribute( 'mode' );
						}
						if( in_array( $m, $usedMatches ) ) {
							continue;
						}
						$usedMatches[] = $m;
					} elseif( $child->hasAttribute( 'name' ) ) {
						$n = $child->getAttribute( 'name' );
						if( $child->hasAttribute( 'mode' ) ) {
							$n .= ':' . $child->getAttribute( 'mode' );
						}
						if( in_array( $n, $usedNames ) ) {
							continue;
						}
						$usedNames[] = $n;
					}
				}
				$dom->documentElement->appendChild( $dom->importNode( $child, true ) );
			}
		}
		return $dom->saveXML();
	}

	/*
	// функция, рекурсивно раскрывающая <xsl:include> в шаблоне.
	private function _extendXSL( $text, $path = '/', $depth = 1 ) {

		if( $depth > 10 ) {
			throw new \Difra\Exception( 'Too long XSLT includes recursion depth.' );
		}
		while( true ) {
			preg_match( '/(.*?)<xsl:include href="(.*?)"\/\>(.*)/is', $text, $matches );
			if( empty( $matches ) ) {
				return $text;
			}
			preg_match( '/<xsl\:stylesheet.*?\>(.*)<\/xsl\:stylesheet\>/is', file_get_contents( $matches[2]{0} != '/' ? "$path/{$matches[2]}"
															    : $matches[2] ), $newMatches );
			if( empty( $newMatches ) ) {
				continue;
			}
			$text = $matches[1] . $this->_extendXSL( $newMatches[1], dirname( $matches[2] ), $depth + 1 ) . $matches[3];
		}
	}
	*/
}
