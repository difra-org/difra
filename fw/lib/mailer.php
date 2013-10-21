<?php

namespace Difra;

/**
 * Class Mailer
 *
 * @package Difra
 */
class Mailer {

	var $fromText = 'Robot';
	var $fromMail = 'robot@example.com';

	/**
	 * Синглтон
	 * @return Mailer
	 */
	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Конструктор
	 */
	public function __construct() {

		$this->fromMail = 'robot@' . Envi::getHost( true );
	}

	/**
	 * Отправляет письмо
	 * @param string      $email                To:
	 * @param string      $subject              Subject:
	 * @param string      $body                 Тело письма
	 * @param string|bool $fromMail             From: (адрес)
	 * @param string|bool $fromText             From: (имя)
	 * @return bool
	 * @throws exception
	 */
	public function sendMail( $email, $subject, $body, $fromMail = false, $fromText = false ) {

		if( !$fromMail ) {
			$fromMail = $this->fromMail;
		}
		if( !$fromText ) {
			$fromText = $this->fromText;
		}

		$headers = array(
			"Mime-Version: 1.0",
			"Content-Type: text/html; charset=\"UTF-8\"",
			"Date: " . date( 'r' ),
			"Message-Id: <" . md5( microtime() ) . '-' . md5( $fromMail . $email ) . '@' . Envi::getHost( true ) . '>',
			'Content-Transfer-Encoding: 8bit'
		);

		// Текстовые строки, если они содержат не-ascii символы, нужно энкодить в base64
		if( preg_match( '/[\\x80-\\xff]+/', $fromText ) ) {
			$headers[] = 'From: =?utf-8?B?' . base64_encode( $fromText ) . "==?= <{$fromMail}>";
		} else {
			$headers[] = "From: $fromText <$fromMail>";
		}
		if( preg_match( '/[\\x80-\\xff]+/', $subject ) ) {
			$subj = '=?utf-8?B?' . base64_encode( $subject ) . '==?=';
		} else {
			$subj = $subject;
		}

		if( !mail( $email, $subj, $body, implode( "\r\n", $headers ) ) ) {
			throw new Exception( "Failed to send message to $email." );
		}
		return true;
	}

	/**
	 * Создаёт и отправляет письмо
	 * Данные передаются в шаблон как аттрибуты корневой ноды <mail>.
	 * В ответе шаблона будут распознаны следующие ноды:
	 * from, fromtext, subject, text
	 *
	 * @param string $email               Адрес
	 * @param string $template            Шаблон письма
	 * @param array  $data                Данные для шаблона
	 */
	public function createMail( $email, $template, $data ) {

		$xml = new \DOMDocument();
		$root = $xml->appendChild( $xml->createElement( 'mail' ) );
		$this->addData( $root, $data );
		$view = new View;
		$templateText = $view->render( $xml, $template, true );

		preg_match( '|<subject[^>]*>(.*)</subject>|Uis', $templateText, $subject );
		preg_match( '|<text[^>]*>(.*)</text>|Uis', $templateText, $mailText );
		preg_match( '|<from[^>]*>(.*)</from>|Uis', $templateText, $fromMail );
		preg_match( '|<fromtext[^>]*>(.*)</fromtext>|Uis', $templateText, $fromText );
		$subject = !empty( $subject[1] ) ? $subject[1] : '';
		$mailText = !empty( $mailText[1] ) ? $mailText[1] : '';
		$fromMail = !empty( $fromMail[1] ) ? $fromMail[1] : $this->fromMail;
		$fromText = !empty( $fromText[1] ) ? $fromText[1] : $this->fromText;

		$this->sendMail( $email, $subject, $mailText, $fromMail, $fromText );
	}

	/**
	 * Добавляет нужные данные в XML для передачи XSL-шаблону, формирующему письмо
	 *
	 * @param \DOMElement|\DOMNode $node
	 * @param array                $data
	 */
	private function addData( $node, $data ) {

		$node->setAttribute( 'host', Envi::getHost( true ) );
		Locales::getInstance()->getLocaleXML( $node );
		foreach( $data as $k => $v ) {
			$node->setAttribute( $k, $v );
		}
	}
}
