<?php

namespace Difra;

class Debugger {

	private $enabled = false;
	static $output = array();
	private $startTime;

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

		if( ( !isset( $_GET['debug'] ) or ( $_GET['debug'] != '0' ) )
		    and isset( $_SERVER['VHOST_DEVMODE'] ) and strtolower( $_SERVER['VHOST_DEVMODE'] ) == 'on'
		) {
			$this->enabled   = true;
			$this->startTime = microtime( true );
			ini_set( 'display_errors', 'Off' );
			ini_set( 'error_reporting', E_ALL );
			ini_set( 'html_errors', ( empty( $_SERVER['REQUEST_METHOD'] ) or Ajax::getInstance()->isAjax ) ? 'Off' : 'On' );
			set_error_handler( array( '\Difra\Debugger', 'captureNormal' ) );
			set_exception_handler( array( '\Difra\Debugger', 'captureException' ) );
			register_shutdown_function( array( '\Difra\Debugger', 'captureShutdown' ) );
		}
	}

	public function isEnabled() {

		return $this->enabled;
	}

	static function addLine( $line ) {

		self::$output[] = array(
			'class'   => 'messages',
			'message' => $line
		);
	}

	static function addEventLine( $line ) {

		self::$output[] = array(
			'class'   => 'events',
			'message' => $line
		);
	}

	public function addDBLine( $type, $line ) {

		if( !$this->enabled ) {
			return;
		}
		self::$output[] = array(
			'class'   => 'db',
			'type'    => $type,
			'message' => $line
		);
	}

	public function addLineAsArray( $array ) {

		if( !$this->enabled ) {
			return;
		}
		self::$output[] = $array;
	}

	public function debugHTML( $standalone = false ) {

		if( !$this->enabled ) {
			return '';
		}
		static $alreadyDidIt = false;
		if( $alreadyDidIt ) {
			return '';
		}
		$alreadyDidIt = true;
		self::addLine( "Page data prepared in " . ( microtime( true ) - $this->startTime ) . " seconds" );
		/** @var $root \DOMElement */
		$xml  = new \DOMDocument();
		$root = $xml->appendChild( $xml->createElement( 'root' ) );
		/** @var $debugNode \DOMElement */
		$debugNode = $root->appendChild( $xml->createElement( 'debug' ) );
		\Difra\Libs\XML\DOM::array2domAttr( $debugNode, self::$output, true );
		if( $standalone ) {
			$root->setAttribute( 'standalone', 1 );
		}
		return View::getInstance()->render( $xml, 'debug', true );
	}

	/**
	 * Callback для эксцепшенов
	 *
	 * @static
	 *
	 * @param \Difra\Exception $exception
	 */
	public static function captureException( $exception ) {

		$err = array(
			'class'         => 'errors',
			'stage'         => 'exception',
			'message'       => $exception->getMessage(),
			'file'          => $exception->getFile(),
			'line'          => $exception->getLine(),
			'traceback'     => $exception->getTrace()
		);
		self::getInstance()->addLineAsArray( $err );
		self::getInstance()->debugHTML();
	}

	/** @var array Типы ошибок, которые ловятся captureNormal — не надо их ловить в captureShutdown */
	private static $handledByNormal = array();

	/**
	 * Callback для ловимых ошибок
	 *
	 * @static
	 *
	 * @param $type
	 * @param $message
	 * @param $file
	 * @param $line
	 *
	 * @return bool
	 */
	public static function captureNormal( $type, $message, $file, $line ) {

		if( !in_array( $type, self::$handledByNormal ) ) {
			self::$handledByNormal[] = $type;
		}
		if( error_reporting() == 0 ) {
			return false;
		}
		$err              = array(
			'class'     => 'errors',
			'type'      => $type,
			'error'     => \Difra\Libs\Debug\errorConstants::getInstance()->getVerbalError( $type ),
			'message'   => $message,
			'file'      => $file,
			'line'      => $line,
			'stage'     => 'normal'
		);
		$err['traceback'] = debug_backtrace();
		@array_shift( $err['traceback'] );
		self::getInstance()->addLineAsArray( $err );
		return false;
	}

	/**
	 * Callback для фатальных ошибок, которые не ловятся другими методами
	 */
	public static function captureShutdown() {

		// произошла ошибка?
		$error = error_get_last();
		if( !$error ) {
			return;
		}
		// это не остатки информации об ошибке, которую уже поймал captureNormal?
		if( !isset( $error['type'] ) or in_array( $error['type'], self::$handledByNormal ) ) {
			return;
		}
		// сохраняем информацию об ошибке
		$error['error']     = \Difra\Libs\Debug\errorConstants::getInstance()->getVerbalError( $error['type'] );
		$error['class']     = 'errors';
		$error['traceback'] = debug_backtrace();
		@array_shift( $error['traceback'] );
		self::getInstance()->addLineAsArray( $error );
		// если по каким-то причинам рендер не случился, отрендерим свою страничку с блэкджеком и шлюхами
		if( !View::getInstance()->rendered ) {
			$controller = Action::getInstance()->controller;
			$ajax       = $controller->ajax;
			if( !$ajax->isAjax ) {
				echo self::getInstance()->debugHTML( true );
			} else {
				echo $ajax->getResponse();
			}
			$controller->view->rendered = true;
		}
	}
}
