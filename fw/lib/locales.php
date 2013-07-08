<?php

namespace Difra;

use Difra\Envi\Setup;

/**
 * Class Locales
 *
 * @package Difra
 */
class Locales {

	public $locale = 'ru_RU';
	/**
	 * @var \DOMDocument
	 */
	public $localeXML = null;
	public $dateFormats = array( 'ru_RU' => 'd.m.y', 'en_US' => 'm-d-y' );
	public $dateTimeFormats = array( 'ru_RU' => 'd.m.y H:i:s', 'en_US' => 'm-d-y h:i:s A' );

	private $loaded = false;

	/**
	 * @static
	 * @param null $locale
	 * @return Locales
	 */
	static function getInstance( $locale = null ) {

		static $locales = array();
		if( !$locale ) {
			$locale = Setup::getLocale();
		}
		if( isset( $locales[$locale] ) ) {
			return $locales[$locale];
		}
		$locales[$locale] = new self( $locale );
		return $locales[$locale];
	}

	/**
	 * Конструктор
	 * @param $locale
	 * @return \Difra\Locales
	 */
	public function __construct( $locale ) {

		$this->locale = $locale;
	}

	/**
	 * Загружает ресурс текущей локали
	 */
	private function load() {

		if( !$this->loaded ) {
			$xml = Resourcer::getInstance( 'locale' )->compile( $this->locale );
			$this->localeXML = new \DOMDocument();
			$this->localeXML->loadXML( $xml );
		}
	}

	/**
	 * Возвращает дерево языковых строк
	 * @param \DOMElement $node
	 * @return void
	 */
	public function getLocaleXML( $node ) {

		$this->load();
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
	 * @param string      $string
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

	/**
	 * Проверяет валидность введенной даты
	 * @param $string
	 * @return bool
	 */
	public function isDate( $string ) {

		if( !$date = $this->parseDate( $string ) ) {
			return false;
		}
		return checkdate( $date[1], $date[2], $date[0] );
	}

	/**
	 * Возвращает дату в формате MySQL
	 *
	 * @param string $string        Дата. Если не указано, будет возвращена текущая дата.
	 * @return bool|string
	 */
	public function getMysqlDate( $string = null ) {

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
	 *
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
	 *
	 * @param string $xpath
	 * @return string|bool
	 */
	public function getXPath( $xpath ) {

		static $simpleXML = null;
		if( is_null( $simpleXML ) ) {
			$this->load();
			$simpleXML = simplexml_import_dom( $this->localeXML );
		}
		$s = $simpleXML->xpath( $xpath );
		if( empty( $s ) and Debugger::isEnabled() ) {
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
	 * Возвращает дату и время в формате текущей локали
	 * @param $timestamp
	 * @return string
	 */
	public function getDateTime( $timestamp ) {

		return date( $this->dateTimeFormats[$this->locale], $timestamp );
	}

	/**
	 * Парсит строку даты в формате MySQL и возвращает её в формате текущей локали
	 *
	 * @param         $date
	 * @param boolean $withTime  - выводить дату вместе с временем
	 * @return string
	 */
	public function getDateFromMysql( $date, $withTime = false ) {

		$date = explode( ' ', $date );
		$date[0] = explode( '-', $date[0] );
		$date[1] = explode( ':', $date[1] );

		if( $withTime ) {
			return $this->getDateTime( mktime( $date[1][0], $date[1][1], $date[1][2], $date[0][1], $date[0][2], $date[0][0] ) );
		}
		return $this->getDate( mktime( $date[1][0], $date[1][1], $date[1][2], $date[0][1], $date[0][2], $date[0][0] ) );
	}

	/**
	 * Создаёт строчку для ссылки
	 * @param string $string
	 * @return string
	 */
	public function makeLink( $string ) {

		$link = '';
		$num = preg_match_all( '/[A-Za-zА-Яа-я0-9Ёё]*/u', $string, $matches );
		if( $num and !empty( $matches[0] ) ) {
			$matches = array_filter( $matches[0], 'strlen' );
			$link = implode( '-', $matches );
		}
		if( $link == '' ) {
			$link = '-';
		}
		return $link;
	}
}


