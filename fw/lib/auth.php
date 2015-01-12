<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra;

/**
 * Адаптер для аутентификации
 * Сохраняет информацию об авторизации в сессиях, производит логин и логаут пользователя
 * Class Auth
 *
 * @package Difra
 */
class Auth {

	public $logged = false;
	public $id = null;
	public $data = null;
	public $moderator = false;
	public $additionals = null;
	public $type = 'user';

	/**
	 * Синглтон
	 *
	 * @return Auth
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Конструктор
	 */
	public function __construct() {

		$this->load();
	}

	/**
	 * Дополнение XML информацией об авторизации
	 *
	 * @param \DOMNode|\DOMElement $node
	 */
	public function getAuthXML( $node ) {

		$authNode = $node->appendChild( $node->ownerDocument->createElement( 'auth' ) );
		if( !$this->logged ) {
			$authNode->appendChild( $node->ownerDocument->createElement( 'unauthorized' ) );
			return;
		} else {
			/** @var \DOMElement $subNode */
			$subNode = $authNode->appendChild( $node->ownerDocument->createElement( 'authorized' ) );
			$subNode->setAttribute( 'id', $this->id );
			$subNode->setAttribute( 'userid', $this->getId() );
			$subNode->setAttribute( 'moderator', $this->moderator );
			$subNode->setAttribute( 'type', $this->type );
			if( !empty( $this->additionals ) ) {
				foreach( $this->additionals as $k => $v ) {
					$subNode->setAttribute( $k, $v );
				}
			}
		}
	}

	/**
	 * Логин
	 *
	 * @param int   $userId
	 * @param array $data
	 * @param array $additionals
	 */
	public function login( $userId, $data = null, $additionals = null ) {

		$this->id = $userId;
		$this->data = $data;
		$this->additionals = $additionals;
		$this->logged = true;
		$this->save();
	}

	/**
	 * Логаут
	 */
	public function logout() {

		$this->id = $this->data = $this->additionals = null;
		$this->logged = false;
		$this->save();
	}

	/**
	 * Обновление информации о пользователе в сессии
	 */
	public function update() {

		$this->save();
	}

	/**
	 * Сохранение текущего состояния авторизации в сессии
	 */
	private function save() {

		\Difra\Envi\Session::start();
		if( $this->logged ) {
			$_SESSION['auth'] = array(
				'id' => $this->id,
				'data' => $this->data,
				'additionals' => $this->additionals
			);
		} else {
			if( isset( $_SESSION['auth'] ) ) {
				unset( $_SESSION['auth'] );
			}
		}
	}

	/**
	 * Загрузка информации об авторизации из сессии
	 *
	 * @return bool
	 */
	private function load() {

		if( !isset( $_SESSION['auth'] ) ) {
			return false;
		}
		$this->id = $_SESSION['auth']['id'];
		$this->data = $_SESSION['auth']['data'];
		$this->additionals = $_SESSION['auth']['additionals'];
		$this->moderator = ( $_SESSION['auth']['data']['moderator'] == 1 ) ? true : false;
		$this->type = isset( $_SESSION['auth']['data']['type'] ) ? $_SESSION['auth']['data']['type'] : 'user';
		return $this->logged = true;
	}

	/**
	 * Возвращает id текущего пользователя или null
	 *
	 * @return int|null
	 */
	public function getId() {

		return isset( $this->data['id'] ) ? $this->data['id'] : null;
	}

	/**
	 * Возвращает тип пользователя
	 *
	 * @return string|null
	 */
	public function getType() {

		return $this->type;
	}

	/**
	 * Определяет, авторизован ли пользователь
	 *
	 * @return bool
	 */
	public function isLogged() {

		return $this->logged;
	}

	/**
	 * Бросает exception, если пользователь не авторизован
	 */
	public function required() {

		if( !$this->logged ) {
			throw new exception( 'Authorization required' );
		}
	}

	/**
	 * Устанавливает дополнительные поля информации о пользователе
	 *
	 * @param array $additionals
	 */
	public function setAdditionals( $additionals ) {

		$this->additionals = $additionals;
		$this->save();
	}

	/**
	 * Возвращает дополнительные поля информации о пользователе
	 *
	 * @return array
	 */
	public function getAdditionals() {

		return $this->additionals;
	}

	/**
	 * Определяет, является ли пользователь модераторо
	 *
	 * @return bool
	 */
	public function isModerator() {

		return $this->moderator;
	}
}
