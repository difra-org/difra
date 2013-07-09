<?php

namespace Difra\Libs\Auth;

use Difra\Envi\Session;
use Difra\View\Exception;

/**
 * Class Digest
 * Поддержка Digest-аутентификации
 *
 * @package Difra\Libs\Auth
 */
class Digest {

	public $realm = 'Restricted area';
	private $users = array();
	private $stale = false;

	static function getInstance( $newUsers = array() ) {

		static $_instance = null;
		if( !$_instance ) {
			$_instance = new self( $newUsers );
		}
		return $_instance;
	}

	public function __construct( $newUsers = array() ) {

		$this->users = $newUsers;
	}

	public function request() {

		header( 'HTTP/1.1 401 Unauthorized' );
		header( 'WWW-Authenticate: Digest realm="' . $this->realm . '",qop="auth",nonce="' . $this->getNonce( true ) . '",opaque="' . md5( $this->realm ) . '"' .
		( $this->stale ? ',stale=TRUE' : '' ) );

		throw new Exception( 401 );
	}

	public function verify() {

		if( !isset( $_SERVER['PHP_AUTH_DIGEST'] )
			or !$data = $this->httpDigestParse( $_SERVER['PHP_AUTH_DIGEST'] )
			or !isset( $this->users[$data['username']] )
		) {
			return false;
		}

		if( $data['nonce'] != $this->getNonce() or !$this->checkNC( $data['nc'] ) ) {
			$this->stale = true;
			return false;
		}

		$A1 = md5( $data['username'] . ':' . $this->realm . ':' . $this->users[$data['username']] );
		$A2 = md5( $_SERVER['REQUEST_METHOD'] . ':' . $data['uri'] );
		$valid_response = md5( $A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2 );
		if( $data['response'] != $valid_response ) {
			return false;
		}
		return true;
	}

	private function httpDigestParse( $txt ) {

		// protect against missing data
		$needed_parts = array( 'nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1 );
		$data = array();

		// php docs use @(\w+)=(?:([\'"])([^\2]+)\2|([^\s,]+))@ regexp, but it doesn't work
		preg_match_all( '@(\w+)=[\'"]?([^\s,\'"]+)@', $txt, $matches, PREG_SET_ORDER );
		foreach( $matches as $m ) {
			$data[$m[1]] = $m[2];
			unset( $needed_parts[$m[1]] );
		}

		return !empty( $needed_parts ) ? false : $data;
	}

	private function getNonce( $regen = false ) {

		Session::start();
		if( $regen ) {
			$key = '';
			for( $i = 0; $i < 16; $i++ ) {
				$key .= chr( rand( 0, 255 ) );
			}
			$_SESSION['digest_nonce'] = bin2hex( $key );
			$_SESSION['digest_nc'] = 0;
		}
		return isset( $_SESSION['digest_nonce'] ) ? $_SESSION['digest_nonce'] : false;
	}

	private function checkNC( $nc ) {

		Session::start();
		if( !isset( $_SESSION['digest_nc'] ) or $_SESSION['digest_nc'] >= $nc ) {
			return false;
		}
		$_SESSION['nc'] = $nc;
		return true;
	}
}
