<?php

namespace Difra\Plugins;

class Twitter {

	private $errorCode = 0;
	private $errorMessage = null;
	private $response = null;

	/**
	 * Адрес на который будет отправлен запрос на создание твита
	 * @var string
	 */
	private static $postUrl = 'https://api.twitter.com/1.1/statuses/update.json';

	/**
	 * Постинг сообщения в твиттер
	 * @param $text
	 */
	public static function post( $text ) {

		$onOff = \Difra\Config::getInstance()->getValue( 'oAuth', 'postToTwitter' );
		if( is_null( $onOff ) || $onOff == '' || $onOff == 0 ) {
			return null;
		}

		$requestFields = array( 'status' => $text );
		$Twitter = new self;
		$Twitter->_performRequest( $requestFields );

		return $Twitter;
	}

	/**
	 * Выполняет запрос
	 * @param array  $fields
	 * @param string $method
	 */
	private function _performRequest( array $fields, $method = 'POST' ) {

		$OAuth = \Difra\Plugins\Twitter\Oauth::build( self::$postUrl );
		$header = $OAuth->getHeader();

		$options = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_HEADER => false,
			CURLOPT_URL => self::$postUrl,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 15,
		);

		if( $method == 'POST' ) {
			$options[CURLOPT_POSTFIELDS] = $fields;
		} else {
			$options[CURLOPT_URL] .= $fields;
		}

		$feed = curl_init();
		curl_setopt_array( $feed, $options );
		$jsonAnswer = curl_exec( $feed );
		curl_close( $feed );

		if( !empty( $jsonAnswer ) ) {
			$this->response = json_decode( $jsonAnswer, true );
		}

		$this->_getErrors( $jsonAnswer );

	}

	/**
	 * Возвращает код ошибки, если она случилась
	 * @return int
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}

	/**
	 * Возвращает текст ошибки.
	 * @return null
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}

	/**
	 * Возвращает ответ сервера после запроса
	 * @return null
	 */
	public function getResponse() {
		return $this->response;
	}

	private function _getErrors( $jsonAnswer ) {

		if( isset( $this->response['errors'] ) && is_array( $this->response['errors'] ) ) {

			foreach( $this->response['errors'] as $k => $data ) {
				if( isset( $data['message'] ) ) {
					$this->errorMessage = $data['message'];
				}
				if( isset( $data['code'] ) ) {
					$this->errorCode = intval( $data['code'] );
				}
			}
		}
	}

}

