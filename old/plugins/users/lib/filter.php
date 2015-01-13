<?php

namespace Difra\Plugins\Users;
use Difra\Libs\Cookies;

class Filter {

	private $filter = null;
	private $sort = array( 'field' => 'registered', 'order' => 'desc' );

	public function __construct() {

		$this->filter = $this->getFilter();
		$this->sort = $this->getSortOrder();
	}

	/**
	 * Устанавливает параметры фильтра
	 * @static
	 * @param array $params
	 */
	public static function setFilter( array $params ) {

		if( !empty( $params ) ) {

			$Cookie = \Difra\Libs\Cookies::getInstance();
			$Cookie->setExpire( time() + '3456000' );
			$Cookie->set( 'users_filter', $params );
		}
	}

	/**
	 * Возвращает значение фильтра
	 * @return array|null
	 */
	public function getFilter() {

		$returnArray = null;

		if( isset( $_COOKIE['users_filter'] ) && !empty( $_COOKIE['users_filter'] ) ) {
			$this->filter = json_decode( $_COOKIE['users_filter'], true );
		}

		return $this->filter;
	}

	/**
	 * Возвращает фильтр для вывода параметров в xml
	 * @param \DOMNode $node
	 */
	public function getFilterXML( \DOMNode $node ) {

		if( !is_null( $this->filter ) ) {
			foreach( $this->filter as $key=>$value ) {
				$node->setAttribute( $key, $value );
			}
		}
	}

	/**
	 * Возвращает массив для поиска
	 * @return array|null
	 */
	private function getConditions() {

		if( empty( $this->filter ) ) {
			return null;
		}
		$returnQuery = null;
		if( isset( $this->filter['active'] ) && $this->filter['active'] == false ) {
			$returnQuery[] = '`active`=1';
		}
		if( isset( $this->filter['ban'] ) && $this->filter['ban'] == false ) {
			$returnQuery[] = '`banned`=0';
		}
		if( isset( $this->filter['moderator'] ) && $this->filter['moderator'] == false ) {
			$returnQuery[] = '`moderator`=0';
		}
		if( isset( $this->filter['noLogin'] ) && $this->filter['noLogin'] == false ) {
			$returnQuery[] = "`logged`<>'0000-00-00 00:00:00'";
		}

		return $returnQuery;
	}

	/**
	 * Устанавливает параметры фильтрации и сортировки пользователей
	 * @param \Difra\Unify\Search $UsersObject
	 */
	public function setConditions( \Difra\Unify\Search &$UsersObject ) {

		$conditions = $this->getConditions();
		if( !is_null( $conditions ) ) {
			$UsersObject->addCustomConditions( $conditions );
		}
		if( $this->sort['order'] == 'desc' ) {
			$UsersObject->setOrder( array( $this->sort['field'] ), array( $this->sort['field'] ) );
		} else {
			$UsersObject->setOrder( array( $this->sort['field'] ) );
		}
	}


	/**
	 * Устанавливает куку с параметрами сортировки
	 * @param $field
	 * @param $order
	 */
	public static function setSortOrder( $field, $order ) {

		$Cookie = \Difra\Libs\Cookies::getInstance();
		$Cookie->setExpire( time() + '3456000' );
		$Cookie->set( 'users_sort', array( 'field' => $field, 'order' => $order ) );
	}

	/**
	 * Возвращает поле и порядок сортировки по нему
	 * @return array
	 */
	public function getSortOrder() {

		if( isset( $_COOKIE['users_sort'] ) && !empty( $_COOKIE['users_sort'] ) ) {
			$this->sort = json_decode( $_COOKIE['users_sort'], true );
		}

		return $this->sort;
	}

	/**
	 * Устанавливает в xml параметры сортровки пользователей в админке
	 * @param \DOMNode $node
	 */
	public function getSortOrderXML( \DOMNode $node ) {

		if( !empty( $this->sort ) && isset( $this->sort['field'] ) && isset( $this->sort['order'] ) ) {
			$node->setAttribute( 'sortField', $this->sort['field'] );
			$node->setAttribute( 'sortOrder', $this->sort['order'] );
		}
	}

}