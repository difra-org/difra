<?php

class Locales {
	public $locale = 'ru_RU';
	public $localeXML = null;
	public $dateFormats = array( 'ru_RU' => 'd.m.y', 'en_US' => 'm-d-y' );

	static function getInstance( $locale = null ) {

		return Site::getInstance()->getLocaleObj( $locale );
	}

	public function __construct( $locale ) {

		$this->locale = $locale;
		if( is_file( $file = DIR_SITE . "locales/{$this->locale}.xml" ) ) {
		} elseif( is_file( $file = DIR_SITE . 'lang.xml' ) ) { // for backwards compartability
		} else {
			error( "Can't find locale/{$this->locale}.xml" );
			return false;
		}
		$this->localeXML = new DOMDocument();
		if( !$this->localeXML->load( $file ) ) {
			$this->localeXML->appendChild( $this->localeXML->createElement( 'locale' ) );
		}
		$this->localeXML->documentElement->setAttribute( 'lang', $locale );

		if( $pluginData = Plugger::getInstance()->getLocales( $locale ) ) {
			foreach( $pluginData as $plugin => $localeFile ) {
				$subXML = new DOMDocument();
				if( !$subXML->load( $localeFile ) ) {
					continue;
				}
				foreach( $subXML->documentElement->childNodes as $item ) {
					$this->localeXML->documentElement->appendChild( $this->localeXML->importNode( $item, true ) );
				}
			}
		}
	}

	public function getLocaleXML( $node ) {

		if( !is_null( $this->localeXML ) ) {
			$node->appendChild( $node->ownerDocument->importNode( $this->localeXML->documentElement, true ) );
		}
	}

	public function setLocale( $locale ) {

		$this->locale = $locale;
	}

	public function parseDate( $string, $locale = false ) {

		$string = str_replace( array( '.', '-' ), '/', $string );
		$pt = explode( '/', $string );
		if( sizeof( $pt ) != 3 ) {
			return false;
		}
		// Возвращает $date[год,месяц,день] в зависимости от локали и dateFormats.
		$date = array( 0, 0, 0 );
		$localeInd = array( 'y' => 0, 'm' => 1, 'd' => 2 );
		$df = $this->dateFormats[$locale ? $locale : $this->locale];
		$df = str_replace( array( '-', '.' ), '/', $df );
		$localePt = explode( '/', $df );
		foreach( $localePt as $ind => $key ) {
			$date[$localeInd[$key]] = $pt[$ind];
		}
		// Приводим год к 4-цифренному формату
		if( $date[0] < 100 ) {
			$date[0] = ( $date[0] < 70 ? 2000 : 1900 ) + $date[0];
		}
		return $date;
	}

	public function isDate( $string ) {

		if( !$date = $this->parseDate( $string ) ) {
			return false;
		}
		return checkdate( $date[1], $date[2], $date[0] );
	}

	public function getMysqlDate( $string ) {

		if( !$string ) {
			return date( '%Y-%m-%d' );
		}
		if( !$date = $this->parseDate( $string ) ) {
			return false;
		}
		return implode( '-', $date );
	}

	public function getMysqlFormat( $locale = false ) {

		$localePt = $this->dateFormats[$locale ? $locale : $this->locale];
		$localePt = str_replace( array( 'd', 'm', 'y' ), array( '%d', '%m', '%Y' ), $localePt );
		return $localePt;
	}

	public function getXPath( $xpath ) {

		static $simpleXML = null;
		if( is_null( $simpleXML ) ) {
			$simpleXML = simplexml_import_dom( $this->localeXML );
		}
		$s = $simpleXML->xpath( $xpath );
		return sizeof( $s ) == 1 ? $s[0] : false;
	}

}


