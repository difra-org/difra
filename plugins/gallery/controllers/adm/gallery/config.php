<?php

class AdmGalleryConfigController extends \Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		/** @var \DOMElement $configNode */
		$configNode = $this->root->appendChild( $this->xml->createElement( 'GalleryConfig' ) );
		$conf = \Difra\Config::getInstance();
		$configNode->setAttribute( 'perpage', $conf->getValue( 'gallery', 'perpage' ) );
		$imgSizes = \Difra\Plugins\Gallery\Album::getSizes();
		$imgSizes2 = array();
		foreach( $imgSizes as $k => $arr ) {
			if( $k == 'f' ) {
				continue;
			}
			$imgSizes2[] = $k . ' ' . implode( ' ', $arr );
		}
		$configNode->setAttribute( 'imgSizes', implode( "\n", $imgSizes2 ) );
		$configNode->setAttribute( 'waterOn', $conf->getValue( 'gallery', 'watermark' ) );
		$configNode->setAttribute( 'waterOnPreview', $conf->getValue( 'gallery', 'waterOnPreview' ) );
		$configNode->setAttribute( 'waterText', $conf->getValue( 'gallery', 'waterText' ) );
		$configNode->setAttribute( 'format', $conf->getValue( 'gallery', 'format' ) );
		if( file_exists( DIR_DATA . 'gallery/watermark.png' ) ) {
			$configNode->setAttribute( 'waterFile', true );
		}
	}

	public function saveAjaxAction( \Difra\Param\AjaxInt $perpage,
					\Difra\Param\AjaxString $imgSizes,
					\Difra\Param\AjaxString $format,
					\Difra\Param\AjaxCheckbox $waterOn,
					\Difra\Param\AjaxCheckbox $waterPreviewOn,
					\Difra\Param\AjaxString $waterText = null,
					\Difra\Param\AjaxFile $waterFile = null ) {

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
			$imgSizes3[$arr[0]] = array( (int)$arr[1], (int)$arr[2] );
		}
		$conf = \Difra\Config::getInstance();
		$conf->setValue( 'gallery', 'perpage', $perpage->val() );
		$conf->setValue( 'gallery', 'imgSizes', $imgSizes3 );
		$conf->setValue( 'gallery', 'watermark', $waterOn->val() );
		$conf->setValue( 'gallery', 'waterOnPreview', $waterPreviewOn->val() );
		$waterText = !is_null( $waterText ) ? $waterText->val() : '';
		$conf->setValue( 'gallery', 'waterText', $waterText );
		$conf->setValue( 'gallery', 'format', $format->val() );

		if( !is_null( $waterFile ) ) {
			$path = DIR_DATA . 'gallery/';
			@mkdir( $path, 0777, true );
			file_put_contents( $path . 'watermark.png', \Difra\Libs\Images::getInstance()->convert( $waterFile->val(), 'png' ) );
		}
		$this->ajax->notify( $this->locale->getXPath( 'gallery/adm/config/saved' ) );
	}
}