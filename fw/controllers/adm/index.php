<?php

/**
 * Class AdmIndexController
 */
class AdmIndexController extends Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$node = $this->root->appendChild( $this->xml->createElement( 'index' ) );

		$statsNode = $node->appendChild( $node->ownerDocument->createElement( 'stats' ) );

		// stats/difra
		/** @var $difraNode \DOMElement */
		$difraNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'difra' ) );
		$ver = \Difra\Envi\Version::getBuild( true );
		$difraNode->setAttribute( 'version', $ver[0] );

		// stats/plugins
		/** @var $pluginsNode \DOMElement */
		$pluginsNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'plugins' ) );
		$plugins = \Difra\Plugger::getAllPlugins();
		$enabledPlugins = $disabledPlugins = array();
		foreach( $plugins as $plugin ) {
			if( $plugin->isEnabled() ) {
				$enabledPlugins[] = $plugin->getName();
			} else {
				$disabledPlugins[] = $plugin->getName();
			}
		}
		$pluginsNode->setAttribute( 'loaded', implode( ', ', $enabledPlugins ) );
		$pluginsNode->setAttribute( 'disabled', implode( ', ', $disabledPlugins ) );

		// stats/cache
		/** @var $cacheNode \DOMElement */
		$cacheNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'cache' ) );
		$cacheNode->setAttribute( 'type', \Difra\Cache::getInstance()->adapter );

		// stats/mysql
		$mysqlNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'mysql' ) );
		try {
			\Difra\MySQL\Parser::getStatusXML( $mysqlNode );
		} catch( Exception $ex ) {
			$mysqlNode->setAttribute( 'error', $ex->getMessage() . ': ' . \Difra\MySQL::getInstance()->getError() );
		}

		// stats/system
		$systemNode = $statsNode->appendChild( $node->ownerDocument->createElement( 'system' ) );
		$systemNode->appendChild( $node->ownerDocument->createElement( 'webserver', $_SERVER['SERVER_SOFTWARE'] ) );
		$systemNode->appendChild( $node->ownerDocument->createElement( 'phpversion', phpversion() ) );

		// stats/extensions
		/** @var $extensionsNode \DOMElement */
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
			'imagick',
			'mysqli'
		);
		foreach( $extensions as $extension ) {
			if( in_array( $extension, $extensionsRequired ) ) {
				$extensionsOk[] = $extension;
				unset( $extensionsRequired[array_search( $extension, $extensionsRequired )] );
			} else {
				$extensionsExtra[] = $extension;
			}
		}
		natcasesort( $extensionsOk );
		natcasesort( $extensionsRequired );
		natcasesort( $extensionsExtra );
		$extensionsNode->setAttribute( 'ok', implode( ', ', $extensionsOk ) );
		$extensionsNode->setAttribute( 'required', implode( ', ', $extensionsRequired ) );
		$extensionsNode->setAttribute( 'extra', implode( ', ', $extensionsExtra ) );

		/** @var $permNode \DOMElement */
		$permNode = $statsNode->appendChild( $statsNode->ownerDocument->createElement( 'permissions' ) );
		if( !is_dir( DIR_DATA ) ) {
			$permNode->setAttribute( 'data', 'Directory ' . DIR_DATA . ' does not exist!' );
		} elseif( !is_writable( DIR_DATA ) ) {
			$permNode->setAttribute( 'data', 'Directory ' . DIR_DATA . ' is not writeable!' );
		}	}

}
