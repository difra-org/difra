<?php

namespace Difra;

class Ajax {

	public $isAjax = false;
	public $parameters = array();
	public $response = array();
	private $actions = array();
	private $problem = false;

	/**
	 * Конструктор
	 */
	public function __construct() {

		$this->isAjax = ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' );
		if( $this->isAjax ) {
			$this->parameters = $this->getRequest();
		}
	}

	/**
	 * Синглтон
	 * @static
	 * @return Ajax
	 */
	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Получает данные от ajaxer
	 * @return array
	 */
	private function getRequest() {

		$res = array();
		if( !empty( $_POST['json'] ) ) {
			$res = json_decode( $_POST['json'], true );
		}
		return $res;
	}

	/**
	 * Возвращает значение параметра или null, если параметр не найден
	 * @param string $name		Имя параметра
	 * @return string|array|null
	 */
	private function getParameter( $name ) {

		return isset( $this->parameters[$name] ) ? $this->parameters[$name] : null;
	}

	/**
	 * Добавляет ajax-ответ
	 * @param string $param		Имя параметра
	 * @param mixed $value		Значение параметра
	 * @return void
	 */
	public function setResponse( $param, $value ) {

		$this->response[$param] = $value;
	}

	/**
	 * Возвращает ответ в json для обработки на стороне клиента
	 * @return string
	 */
	public function getResponse() {

		if( !empty( $this->actions ) ) {
			$this->setResponse( 'actions', $this->actions );
		}
		return json_encode( $this->response );
	}

	/**
	 *
	 * Действия с ajaxer
	 *
	 */

	/**
	 * Добавляет специальный ответ
	 * @param array $action		Массив с action (типом ответа) и нужными данными
	 * @return void
	 */
	private function addAction( $action ) {

		$this->actions[] = $action;
	}

	/**
	 * Возвращает true, если в action'ах есть действия с ошибками обработки формы
	 * @return bool
	 */
	public function hasProblem() {

		return $this->problem;
	}

	/**
	 * Показать сообщение
	 * @param string $message	Текст сообщения
	 * @return void
	 */
	public function notify( $message ) {

		$this->addAction( array(
				       'action' => 'notify', 'message' => htmlspecialchars( $message, ENT_IGNORE, 'UTF-8' ),
				       'lang' => array(
					       'close' => Locales::getInstance()->getXPath( 'notifications/close' )
				       )
				  ) );
	}

	/**
	 * Показать ошибку
	 * @param string $message	Текст ошибки
	 * @return void
	 */
	public function error( $message ) {

		$this->addAction( array(
				       'action' => 'error', 'message' => htmlspecialchars( $message, ENT_IGNORE, 'UTF-8' ), 'lang' => array(
						  'close' => Locales::getInstance()->getXPath( 'notifications/close' )
					  )
				  ) );
	}

	/**
	 * Не заполнено необходимое поле
	 * @param string $name		Имя (name) элемента формы, который нужно заполнить
	 * @return void
	 */
	public function required( $name ) {

		$this->problem = true;
		$this->addAction( array(
				       'action' => 'require', 'name' => $name
				  ) );
	}

	/**
	 * Не корректные данные формы
	 * @param string $name		Имя (name) элемента формы, заполненного не верно
	 * @return void
	 */
	public function invalid( $name ) {

		$this->problem = true;
		$this->addAction( array(
				       'action' => 'invalid', 'name' => $name
				  ) );
	}

	/**
	 * Перенаправление
	 * @param string $url		URL, по которому будет сделано перенаправление
	 * @return void
	 */
	public function redirect( $url ) {

		$this->addAction( array(
				       'action' => 'redirect', 'url' => $url
				  ) );
	}

	/**
	 * Перегрузить текущую страницу
	 * @return void
	 */
	public function reload() {

		$this->addAction( array(
				       'action' => 'reload'
				  ) );
	}

	/**
	 * Создать оверлей со следующим html-содержимым
	 * @param string $html		Содержимое innerHTML оверлея
	 * @return void
	 */
	public function display( $html ) {

		$this->addAction( array(
				       'action' => 'display', 'html' => $html
				  ) );
	}

	/**
	 * Записать содержимое $html в элемент $target
	 * @param string $target	Селектор элемента в формате jQuery, например '#targetId'
	 * @param string $html		Содержимое для innerHTML
	 * @return void
	 */
	public function load( $target, $html ) {

		$this->addAction( array(
				       'action' => 'load', 'target' => $target, 'html' => $html
				  ) );
	}
}

