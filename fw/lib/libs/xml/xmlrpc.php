<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Libs\XML;

/**
 * Класс для организации интерфейсов XML-RPC
 */
class XMLRPC {

	/**
	 * Синглтон
	 *
	 * @static
	 * @return self
	 */
	static function getInstance() {

		static $_instance = null;
		if( !$_instance ) {
			$_instance = new self;
		}
		return $_instance;
	}

	/**
	 * Обращается к удалённому серверу по протоколу XML-RPC.
	 *
	 * @param string $url    адрес XML-RPC сервера
	 * @param string $method имя метода
	 * @param        $params передаваемые параметры
	 *
	 * @return mixed результат работы удалённой процедуры или ошибка в формате:
	 * array( "faultString" => "server error. method not found. te1st", "faultCode" => -32601 )
	 */
	public function sendRequest( $url, $method, $params ) {

		// assemble POST body
		$request = xmlrpc_encode_request( $method, $params );

		// make request
		$cl = curl_init( $url );
		curl_setopt( $cl, CURLOPT_CRLF, 1 );
		curl_setopt( $cl, CURLOPT_POST, 1 );
		curl_setopt( $cl, CURLOPT_POSTFIELDS, $request );
		curl_setopt( $cl, CURLOPT_RETURNTRANSFER, 1 );
		$contents = curl_exec( $cl );

		// decode answer
		$result = xmlrpc_decode( $contents );

		return $result;
	}

	/**
	 * Функция, выполняющая обработку XML-RPC запроса.
	 *
	 * @param            $handler экземпляр класса удалённых процедур. Имена методов должны соответствовать именам
	 *                            запрашиваемых методов, либо класс должен именть magic метод __call()
	 * @param array|bool $methods список доступных методов (если не указан, класс должен иметь метод getMethods(),
	 *                            возвращающий соответствующий список)
	 *
	 * @return string
	 */
	public function processRequest( $handler, $methods = false ) {

		$server = xmlrpc_server_create();

		if( !$methods ) {
			$methods = $handler->getMethods();
		}
		foreach( $methods as $method ) {
			xmlrpc_server_register_method( $server, $method, array( $handler, $method ) );
		}
		$request = ( isset ( $HTTP_RAW_POST_DATA ) and $HTTP_RAW_POST_DATA ) ? $HTTP_RAW_POST_DATA : file_get_contents( 'php://input' );

		$response = xmlrpc_server_call_method( $server, $request, null );
		xmlrpc_server_destroy( $server );
		return $response;
	}
}
