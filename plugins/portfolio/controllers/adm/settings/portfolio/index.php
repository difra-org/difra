<?php

class AdmSettingsPortfolioIndexController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}


	public function indexAction() {

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'PortfolioSettings' ) );
		$imgSizes = \Difra\Plugins\Portfolio::getSizes();
		$imgSizes2 = array();
		foreach( $imgSizes as $k => $arr ) {
			if( $k == 'f' ) {
				continue;
			}
			$imgSizes2[] = $k . ' ' . implode( ' ', $arr );
		}
		$mainXml->setAttribute( 'imgSizes', implode( "\n", $imgSizes2 ) );
	}

	public function saveAjaxAction( \Difra\Param\AjaxString $imgSizes ) {

		$imgSizes2 = explode( "\n", str_replace( "\r", '', $imgSizes ) );
		$imgSizes3 = array();
		foreach( $imgSizes2 as $str ) {
			$arr = explode( ' ', $str );
			if( sizeof( $arr ) != 3 or !ctype_alpha( $arr[0] ) or !ctype_digit( $arr[1] ) or !ctype_digit( $arr[2] ) or $arr[0] == 'f'
				or isset( $imgSizes3[$arr[0]] ) or !$arr[1] or !$arr[2]
			) {
				$this->ajax->invalid( 'imgSizes' );
				$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'portfolio/adm/notify/badImageSizes' ) );
				return;
			}
			$imgSizes3[$arr[0]] = array( (int)$arr[1], (int)$arr[2] );

			$Config = \Difra\Config::getInstance();
			$Config->setValue( 'portfolio_settings', 'imgSizes', $imgSizes3 );
		}

		$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'portfolio/adm/notify/settingSaved' ) );
		$this->ajax->refresh();
	}

}