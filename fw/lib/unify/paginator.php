<?php

namespace Difra\Unify;

use Difra\Exception;

/**
 * Пагинатор
 * Class Paginator
 *
 * @package Difra\Unify
 */
class Paginator {

	/** @var int Количество записей на страницу */
	protected $perpage = 20;

	/** @var int|null Номер текущей страницы */
	protected $page = null;

	/** @var int Количество найденных элементов */
	protected $total = null;

	/** @var int Количество страниц */
	protected $pages = null;

	/** @var string Префикс для ссылки */
	protected $linkPrefix = '';

	/** @var string|bool Символ для формирования get-параметра, например '?' -> $linkPrefix?page=$page, иначе ссылка будет вида $linkPrefix/page/$page */
	protected $get = false;

	public function __construct() {

		$this->linkPrefix = \Difra\Envi\Action::getControllerUri();
	}

	/**
	 * Возвращает строку для LIMIT
	 *
	 * @return string
	 */
	public function getPaginatorLimit() {

		return array( ( $this->page - 1 ) * $this->perpage, $this->perpage );
	}

	/**
	 * Установка общего количества элементов
	 * @param int $count
	 */
	public function setTotal( $count ) {

		$this->total = $count;
		$this->pages = floor( ( $count - 1 ) / $this->perpage ) + 1;
	}

	public function getPages() {

		return $this->pages;
	}

	/**
	 * Добавляет ноду пагинатора в XML
	 *
	 * @param \DOMNode $node
	 */
	public function getPaginatorXML( $node ) {

		/** @var \DOMElement $pNode */
		$pNode = $node->appendChild( $node->ownerDocument->createElement( 'paginator' ) );
		$pNode->setAttribute( 'current', $this->page );
		$pNode->setAttribute( 'pages', $this->pages );
		$pNode->setAttribute( 'link', $this->linkPrefix );
		$pNode->setAttribute( 'get', $this->get );
	}

	/**
	 * Установить текущую страницу
	 * @param $page
	 * @throws \Difra\Exception
	 */
	public function setPage( $page ) {

		if( !ctype_digit( (string)"$page" ) or $page < 1 ) {
			throw new Exception( "Expected page number as parameter" );
		}
		$this->page = (int)$page;
	}

	/**
	 * Установить символ для get-параметра
	 *
	 * @param string $get
	 */
	public function setGet( $get ) {

		$this->get = $get;
	}

	/**
	 * Префикс для ссылки
	 * @param string $linkPrefix
	 */
	public function setLinkPrefix( $linkPrefix ) {

		$this->linkPrefix = $linkPrefix;
	}

	/**
	 * Установка количества выводимых элементов на страницу результата
	 * @param int $perpage
	 */
	public function setPerpage( $perpage ) {

		$this->perpage = $perpage;
	}
}
