<?php

namespace Difra\Adm;

class Stats {

	public static function getInstance() {
		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	function getXML( $node ) {

		$statsNode = $node->appendChild( $node->ownerDocument->createElement( 'stats' ) );

		// stats/difra
		$difraNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'difra' ) );
		$ver = \Difra\Site::getInstance()->getBuild( true );
		$difraNode->setAttribute( 'version', $ver[1] );

		// stats/plugins
		$difraNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'plugins' ) );
		$difraNode->setAttribute( 'loaded', implode( ', ', \Difra\Plugger::getInstance()->getList() ) );
		$difraNode->setAttribute( 'disabled', implode( ', ', \Difra\Plugger::getInstance()->getDisabled() ) );

		// stats/system
		$systemNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'system' ) );
		$systemNode->appendChild( $node->ownerDocument->createElement( 'webserver', $_SERVER['SERVER_SOFTWARE'] ) );
		$systemNode->appendChild( $node->ownerDocument->createElement( 'phpversion', phpversion() ) );

		// stats/extensions
		$extensionsNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'extensions' ) );
		$extensions = get_loaded_extensions();
		$extensionsOk = array();
		$extensionsExtra = array();
		$extensionsRequired = array(
			'dom',
			'SimpleXML',
			'xsl',
			'zlib',
			'ctype',
			'json',
			'mbstring',
			'Reflection',
			'Phar',
			'gd',
			'imagick',
			'mysqli'
		);
		foreach( $extensions as $extension ) {
			if( in_array( $extension, $extensionsRequired ) ) {
				$extensionsOk[] = $extension;
				unset( $extensionsRequired[ array_search( $extension, $extensionsRequired ) ] );
			} else {
				$extensionsExtra[] = $extension;
			}
		}
		$extensionsNode->setAttribute( 'ok', implode( ', ', $extensionsOk ) );
		$extensionsNode->setAttribute( 'required', implode( ', ', $extensionsRequired ) );
		$extensionsNode->setAttribute( 'extra', implode( ', ', $extensionsExtra ) );
	}

}