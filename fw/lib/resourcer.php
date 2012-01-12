<?php

namespace Difra;

class Resourcer {

	/**
	 * @static
	 * @param $type
	 * @param bool $quiet
	 * @return Resourcer\CSS|Resourcer\JS|Resourcer\XSLT|Resourcer\Menu|Resourcer\Locale
	 * @throws exception
	 */
	static public function getInstance( $type, $quiet = false ) {

		// TODO: перевести все классы на неймспейсы
		switch( $type ) {
			case 'css':
				return Resourcer\CSS::getInstance();
			case 'js':
				return Resourcer\JS::getInstance();
			case 'xslt':
				return Resourcer\XSLT::getInstance();
			case 'menu':
				return Resourcer\Menu::getInstance();
			case 'locale':
				return Resourcer\Locale::getInstance();
			default:
				if( !$quiet ) {
					throw new exception( "Resourcer does not support resource type '$type'" );
				}
		}
	}

}