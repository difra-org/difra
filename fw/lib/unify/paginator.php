<?php

namespace Difra\Unify;

/**
 * Пагинатор
 * Class Paginator
 *
 * @package Difra\Unify
 */
class Paginator {

	public $perpage = 20;
	public $page = 1;

	public $total = null;
	public $pages = null;

	public $linkPrefix = '';

	public $get = false;

	/**
	 * Возвращает строку для LIMIT
	 *
	 * @return string
	 */
	public function getLimit() {

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

	/**
	 * Добавляет ноду пагинатора в XML
	 *
	 * @param \DOMNode $node
	 */
	public function getXML( $node ) {

		/** @var \DOMElement $pNode */
		$pNode = $node->appendChild( $node->ownerDocument->createElement( 'paginator' ) );
		$pNode->setAttribute( 'current', $this->page );
		$pNode->setAttribute( 'pages', $this->pages );
		$pNode->setAttribute( 'link', $this->linkPrefix );
		$pNode->setAttribute( 'get', $this->get );
	}
}
