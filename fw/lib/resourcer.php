<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra;

/**
 * Class Resourcer
 *
 * @package Difra
 */
class Resourcer {

	/**
	 * Фабрика сборщиков ресурсов
	 *
	 * @param string $type
	 * @param bool   $quiet
	 *
	 * @return Resourcer\CSS|Resourcer\JS|Resourcer\XSLT|Resourcer\Menu|Resourcer\Locale
	 * @throws Exception
	 */
	static public function getInstance( $type, $quiet = false ) {

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
				throw new Exception( "Resourcer does not support resource type '$type'" );
			}
			return null;
		}
	}
}