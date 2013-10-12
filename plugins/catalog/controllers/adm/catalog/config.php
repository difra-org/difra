<?php

class AdmCatalogConfigController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		/** @var \DOMElement $configNode */
		$configNode = $this->root->appendChild( $this->xml->createElement( 'CatalogConfig' ) );
		$conf = \Difra\Config::getInstance();
		$configNode->setAttribute( 'maxdepth', $conf->getValue( 'catalog', 'maxdepth' ) );
		$configNode->setAttribute( 'perpage', $conf->getValue( 'catalog', 'perpage' ) );
		$configNode->setAttribute( 'hideempty', $conf->getValue( 'catalog', 'hideempty' ) );
		$configNode->setAttribute( 'allownoimage', $conf->getValue( 'catalog', 'allownoimage' ) );
		$configNode->setAttribute( 'usescale', $conf->getValue( 'catalog', 'usescale' ) );
		$imgSizes = \Difra\Plugins\Catalog\Item::getSizes();
		$imgSizes2 = array();
		foreach( $imgSizes as $k => $arr ) {
			if( $k == 'f' ) {
				continue;
			}
			$imgSizes2[] = $k . ' ' . implode( ' ', $arr );
		}
		$configNode->setAttribute( 'imgSizes', implode( "\n", $imgSizes2 ) );

	}

	public function saveAjaxAction( \Difra\Param\AjaxInt $maxdepth, \Difra\Param\AjaxInt $perpage, \Difra\Param\AjaxString $imgSizes,
					\Difra\Param\AjaxCheckbox $hideempty, \Difra\Param\AjaxCheckbox $allownoimage,
					\Difra\Param\AjaxCheckbox $usescale ) {

		$imgSizes2 = explode( "\n", str_replace( "\r", '', $imgSizes ) );
		$imgSizes3 = array();
		foreach( $imgSizes2 as $str ) {
			$arr = explode( ' ', $str );
			if( sizeof( $arr ) != 3 or !ctype_alpha( $arr[0] ) or !ctype_digit( $arr[1] ) or !ctype_digit( $arr[2] ) or $arr[0] == 'f'
			    or isset( $imgSizes3[$arr[0]] ) or !$arr[1] or !$arr[2]
			) {
				$this->ajax->invalid( 'imgSizes' );
				return;
			}
			$imgSizes3[$arr[0]] = array( (int) $arr[1], (int) $arr[2] );
		}
		$conf = \Difra\Config::getInstance();
		$conf->setValue( 'catalog', 'maxdepth', $maxdepth->val() );
		$conf->setValue( 'catalog', 'perpage', $perpage->val() );
		$conf->setValue( 'catalog', 'hideempty', $hideempty->val() );
		$conf->setValue( 'catalog', 'imgSizes', $imgSizes3 );
		$conf->setValue( 'catalog', 'allownoimage', $allownoimage->val() );
		$conf->setValue( 'catalog', 'usescale', $usescale->val() );
		$this->ajax->notify( $this->locale->getXPath( 'catalog/adm/config/saved' ) );
	}
}