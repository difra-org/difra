<?php

namespace Difra;

class Locales {
	public $locale = 'ru_RU';
	public $localeXML = null;
	public $dateFormats = array( 'ru_RU' => 'd.m.y', 'en_US' => 'm-d-y' );

	/**
	 * @static
	 * @param null $locale
	 * @return Locales
	 */
	static function getInstance( $locale = null ) {

		return Site::getInstance()->getLocaleObj( $locale );
	}

	public function __construct( $locale ) {

		$this->locale = $locale;
		$xml = Resourcer::getInstance( 'locale' )->compile( $this->locale );
		$this->localeXML = new \DOMDocument();
		$this->localeXML->loadXML( $xml );
	}

	/**
	 * Возвращает дерево языковых строк
	 * @param $node
	 * @return void
	 */
	public function getLocaleXML( $node ) {

		if( !is_null( $this->localeXML ) ) {
			$node->appendChild( $node->ownerDocument->importNode( $this->localeXML->documentElement, true ) );
		}
	}

	/**
	 * Меняет текущую локаль
	 * @param string $locale
	 * @return void
	 */
	public function setLocale( $locale ) {

		$this->locale = $locale;
	}

	/**
	 * Парсит строку даты, вводимую пользователем и возвращает в формате:
	 * array( 0 => Y, 1 => m, 2 => d );
	 *
	 * @param string $string
	 * @param string|bool $locale
	 * @return array|bool
	 */
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

	/**
	 * Возвращает строку в синтаксисе MySQL для получения дат в формате локали из базы данных
	 * @param bool $locale
	 * @return mixed
	 */
	public function getMysqlFormat( $locale = false ) {

		$localePt = $this->dateFormats[$locale ? $locale : $this->locale];
		$localePt = str_replace( array( 'd', 'm', 'y' ), array( '%d', '%m', '%Y' ), $localePt );
		return $localePt;
	}

	/**
	 * Возвращает строчку из языковых файлов по её XPath
	 * @param string $xpath
	 * @return string|bool
	 */
	public function getXPath( $xpath ) {

		static $simpleXML = null;
		if( is_null( $simpleXML ) ) {
			$simpleXML = simplexml_import_dom( $this->localeXML );
		}
		$s = $simpleXML->xpath( $xpath );
		if( empty( $s ) and Debugger::getInstance()->isEnabled() ) {
			$s = array( 'No language item for: ' . $xpath );
		}
		return sizeof( $s ) ? (string)$s[0] : false;
	}

	/**
	 * Возвращает дату в формате текущей локали
	 * @param int $timestamp
	 * @return string
	 */
	public function getDate( $timestamp ) {
		
		return date( $this->dateFormats[$this->locale], $timestamp );
	}

	/**
	 * Парсит строку даты в формате MySQL и возвращает её в формате текущей локали
	 * @param $date
	 * @return string
	 */
	public function getDateFromMysql( $date ) {

		$date = explode( ' ', $date );
		$date[0] = explode( '-', $date[0] );
		$date[1] = explode( ':', $date[1] );
		return $this->getDate( mktime( $date[1][0], $date[1][1], $date[1][2], $date[0][1], $date[0][2], $date[0][0] ) );
	}

}


