<?php

namespace Difra;

class Mailer {

	var $fromText = 'Robot';
	var $fromMail = 'robot@example.com';

	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

		$this->fromMail = 'robot@' . $_SERVER['HTTP_HOST'];
	}

	public function sendMail( $email, $subject, $body, $fromMail = false, $fromText = false ) {

		if( !$fromMail ) {
			$fromMail = $this->fromMail;
		}
		if( !$fromText ) {
			$fromText = $this->fromText;
		}

		$headers =
			"Mime-Version: 1.0\r\n" .
			"Content-Type: text/html; charset=\"utf-8\"\r\n" .
			"Content-Transfer-Encoding: 8bit\r\n" .
			'From: =?utf-8?B?' . base64_encode( $fromText ) . "==?= <{$fromMail}>";
		$subj = '=?utf-8?B?' . base64_encode( $subject ) . '==?=';
		if( !mail( $email, $subj, $body, $headers ) ) {
			throw new exception( "Failed to send message to $email." );
		}
		return true;
	}

	public function createMail( $email, $template, $data ) {

		$xml = new \DOMDocument();
		$root = $xml->appendChild( $xml->createElement( 'mail' ) );
		$this->_addDataXML( $root );
		Locales::getInstance()->getLocaleXML( $root );
		foreach( $data as $k => $v ) {
			$root->setAttribute( $k, $v );
		}
		$view = new View;
		$templateText = $view->render( $xml, $template, true );

		preg_match( '|<subject[^>]*>(.*)</subject>|Uis', $templateText, $subject );
		preg_match( '|<text[^>]*>(.*)</text>|Uis', $templateText, $mailText );
		preg_match( '|<from[^>]*>(.*)</from>|Uis', $templateText, $fromMail );
		preg_match( '|<fromtext[^>]*>(.*)</fromtext>|Uis', $templateText, $fromText );
		$subject  = !empty( $subject[1] )  ? $subject[1]  : '';
		$mailText = !empty( $mailText[1] ) ? $mailText[1] : '';
		$fromMail = !empty( $fromMail[1] ) ? $fromMail[1] : $this->fromMail;
		$fromText = !empty( $fromText[1] ) ? $fromText[1] : $this->fromText;

		$this->sendMail( $email, $subject, $mailText, $fromMail, $fromText );
	}

	private function _addDataXML( $node ) {

		$node->setAttribute( 'host', Site::getInstance()->getMainhost() );
	}
}
