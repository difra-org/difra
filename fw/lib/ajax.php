<?php

namespace Difra;

class Ajax {

	public $isAjax = false;
	public $isIframe = false;
	public $parameters = array();
	public $response = array();
	private $actions = array();
	private $problem = false;

	/**
	 * Конструктор
	 */
	public function __construct() {

		if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
			$this->isAjax = true;
			$parameters = $this->getRequest();
			if( empty( $parameters ) ) {
				return;
			}
			foreach( $parameters as $k => $v ) {
				$this->parseParam( $this->parameters, $k, $v );
			}
		} elseif( isset( $_POST['_method'] ) and $_POST['_method'] == 'iframe' ) {
			$this->isAjax = true;
			$this->isIframe = true;
			$this->parameters = $_POST;
			unset( $this->parameters['method_'] );
			if( !empty( $_FILES ) ) {
				foreach( $_FILES as $k => $files ) {
					if( isset( $files['name'] ) and !is_array( $files['name'] ) ) {
						$this->parseParam( $this->parameters, $k, $files );
						continue;
					}
					if( substr( $k, -2 ) != '[]' ) {
						$k = $k . '[]';
					}
					if( isset( $files['name'] ) and is_array( $files['name'] ) ) {
						$files2 = $files;
						$files = array();
						foreach( $files2['name'] as $k2 => $v2 ) {
							$files[] = array(
								'name' => $v2,
								'type' => $files2['type'][$k2],
								'tmp_name' => $files2['tmp_name'][$k2],
								'error' => $files2['error'][$k2],
								'size' => $files2['size'][$k2]
							);
						}
					}
					foreach( $files as $file ) {
						if( $file['error'] == UPLOAD_ERR_NO_FILE ) {
							continue;
						}
						$this->parseParam( $this->parameters, $k, $file );
					}
				}
			}
		}
	}

	private function parseParam( &$arr, $k, $v ) {

		$keys = explode( '[', $k );
		if( sizeof( $keys ) == 1 ) {
			$arr[$k] = $v;
			return;
		}
		for( $i = 1; $i < sizeof( $keys ); $i++ ) {
			if( $keys[$i]{strlen($keys[$i])-1} == ']' ) {
				$keys[$i] = substr( $keys[$i], 0, -1 );
			}
		}
		$this->putParam( $arr, $keys, $v );
	}

	private function putParam( &$arr, $keys, $v ) {

		if( empty( $keys ) ) {
			$arr = $v;
			return;
		}
		$k = array_shift( $keys );
		if( $k ) {
			if( !isset( $arr[$k] ) ) {
				$arr[$k] = array();
			}
			$arr2 = &$arr[$k];
		} else {
			$arr[] = array();
			end( $arr );
			$arr2 = &$arr[key( $arr )];
		}
		$this->putParam( $arr2, $keys, $v );
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
			if( !empty( $res['form'] ) ) {
				foreach( $res['form'] as $item ) {
					$res[$item['name']] = trim( $item['value'] );
				}
				unset( $res['form'] );
			}
		}
		return $res;
	}

	/**
	 * Возвращает значение параметра или null, если параметр не найден
	 * @param string $name		Имя параметра
	 * @return string|array|null
	 */
	public function getParam( $name ) {

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
	 * @param string $message	Текст ошибки
	 * @return void
	 */
	public function invalid( $name, $message = null ) {

		$this->problem = true;
		$action = array( 'action' => 'invalid', 'name' => $name );
		if( $message ) {
			$action['message'] = $message;
		}
		$this->addAction( $action );
	}

	/**
	 * Сообщение рядом с элементом формы
	 * @param $name
	 * @param $message
	 * @param $class
	 * @return void
	 */
	public function status( $name, $message, $class ) {

		$this->addAction( array(
					  'action' => 'status',
					  'name' => $name,
					  'message' => $message,
					  'classname' => $class
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
	 * Мягко обновить текущую страницу
	 */
	public function refresh() {

		$this->redirect( $_SERVER['HTTP_REFERER'] );
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

	public function close() {

		$this->addAction( array(
					'action' => 'close'
				  ) );
	}

	public function reset() {

		$this->addAction( array(
				       'action' => 'reset'
				  ) );
	}
}

