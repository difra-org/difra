<?php
	
	class Resourcer {
		
		static public function getInstance( $type, $quiet = false ) {
			
			switch( $type ) {
			case 'css':
				return Resourcer_CSS::getInstance();
			case 'js':
				return Resourcer_JS::getInstance();
			case 'xslt':
				return Resourcer_XSLT::getInstance();
			case 'menu':
				return Resourcer_Menu::getInstance();
			case 'locale':
				return Resourcer_Locale::getInstance();
			default:
				if( !$quiet ) {
					throw new exception( "Resourcer does not support resource type '$type'" );
				}
			}
		}
		
	}