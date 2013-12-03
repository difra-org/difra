<?php

namespace Difra;

/**
 * Class Debugger
 *
 * @package Difra
 */
class Debugger {

	private static $enabled = self::DEBUG_DISABLED;
	const DEBUG_DISABLED = 0;
	const DEBUG_ENABLED = 1;

	private static $console = self::CONSOLE_NONE;
	const CONSOLE_NONE = 0; // console disabled
	const CONSOLE_DISABLED = 1; // console enabled, but not active
	const CONSOLE_ENABLED = 2; // console enabled and active

	private static $caches = self::CACHES_ENABLED;
	const CACHES_DISABLED = 0;
	const CACHES_ENABLED = 1;

	private static $output = array();
	private static $hadError = false;

	public static function init() {

		static $initDone = false;
		if( $initDone ) {
			return;
		}
		$initDone = true;

		if( Envi::getMode() != 'web' ) {
			self::$caches = self::CACHES_DISABLED;
			return;
		}
		if( !isset( $_SERVER['VHOST_DEVMODE'] ) or strtolower( $_SERVER['VHOST_DEVMODE'] ) != 'on' ) {
			ini_set( 'display_errors', 'Off' );
			return;
		}

		// дебаг отключен?
		if( isset( $_GET['debug'] ) and $_GET['debug'] == -1 ) {
			ini_set( 'display_errors', 'On' );
			ini_set( 'error_reporting', E_ALL );
			ini_set( 'html_errors',
				 ( Envi::getMode() != 'web' or Ajax::getInstance()->isAjax ) ? 'Off' : 'On' );
			self::$enabled = self::CONSOLE_DISABLED;
			self::$console = self::CONSOLE_NONE;
			return;
		}
		if( ( isset( $_GET['debug'] ) and !$_GET['debug'] ) or ( isset( $_COOKIE['debug'] ) and !$_COOKIE['debug'] ) ) {
			self::$enabled = self::DEBUG_DISABLED;
		} else {
			self::$enabled = self::DEBUG_ENABLED;
		}
		// консоль отключена?
		if( !self::$enabled or ( isset( $_COOKIE['debugConsole'] ) and !$_COOKIE['debugConsole'] ) ) {
			self::$console = self::CONSOLE_DISABLED;
		} else {
			self::$console = self::CONSOLE_ENABLED;
		}
		// кэши включены?
		if( !isset( $_COOKIE['cachesEnabled'] ) or !$_COOKIE['cachesEnabled'] ) {
			self::$caches = self::CACHES_DISABLED;
		}

		if( self::$console == self::CONSOLE_ENABLED ) {
			// console is active, so we intercept errors
			ini_set( 'display_errors', 'Off' );
			ini_set( 'html_errors', 'Off' );
			ini_set( 'error_reporting', E_ALL );
			set_error_handler( array( '\Difra\Debugger', 'captureNormal' ) );
			set_exception_handler( array( '\Difra\Debugger', 'captureException' ) );
			register_shutdown_function( array( '\Difra\Debugger', 'captureShutdown' ) );
		} else {
			// console is inactive, so enable errors output
			ini_set( 'display_errors', 'On' );
			ini_set( 'error_reporting', E_ALL );
			ini_set( 'html_errors', Ajax::getInstance()->isAjax ? 'Off' : 'On' );
		}
	}

	/**
	 * Включен ли режим отладки
	 * @return bool
	 */
	public static function isEnabled() {

		self::init();
		return (bool)self::$enabled;
	}

	/**
	 * Включена ли отладочная консоль?
	 * 0 — отладка полностью отключена
	 * 1 — отключен отлов ошибок
	 * 2 — консоль включена
	 * @return int
	 */
	public static function isConsoleEnabled() {

		self::init();
		return self::$console;
	}

	/**
	 * Включены ли кэши?
	 * @return bool
	 */
	public static function isCachesEnabled() {

		self::init();
		return (bool)self::$caches;
	}

	/**
	 * Добавляет сообщение в лог для консоли
	 * @param string $line
	 */
	public static function addLine( $line ) {

		self::$output[] = array(
			'class' => 'messages',
			'message' => $line,
			'timer' => self::getTimer()
		);
	}

	/**
	 * Добавляет событие в лог для консоли
	 * @param string $line
	 */
	public static function addEventLine( $line ) {

		self::$output[] = array(
			'class' => 'events',
			'message' => $line,
			'timer' => self::getTimer()
		);
	}

	/**
	 * Добавляет запрос в БД в лог для консоли
	 * @param string $type
	 * @param string $line
	 */
	public static function addDBLine( $type, $line ) {

		if( !self::$enabled ) {
			return;
		}
		self::$output[] = array(
			'class' => 'db',
			'type' => $type,
			'message' => $line,
			'timer' => self::getTimer()
		);
	}

	/**
	 * Добавить ошибку в лог для консоли
	 * @param $array
	 */
	public static function addLineAsArray( $array ) {

		if( !self::$enabled ) {
			return;
		}
		if( $array['class'] == 'errors' ) {
			self::$hadError = true;
		}
		$array['timer'] = self::getTimer();
		self::$output[] = $array;
	}

	/**
	 * Рендер HTML отладочной консоли
	 *
	 * @param bool $standalone      Консоль выводится на отдельной странице (если произошла фатальная ошибка и запрошенная страница
	 *                                 не может быть отрендерена)
	 *
	 * @return string
	 */
	public static function debugHTML( $standalone = false ) {

		static $alreadyDidIt = false;
		if( $alreadyDidIt ) {
			return '';
		}
		/** @var $root \DOMElement */
		$xml = new \DOMDocument();
		$root = $xml->appendChild( $xml->createElement( 'root' ) );
		self::debugXML( $root, $standalone );

		return View::render( $xml, 'all', true );
	}

	/**
	 * Добавляет в XML-ноду данные о дебаге и для дебаг-панели
	 *
	 * @param \DOMNode|\DOMElement $node
	 * @param bool                 $standalone
	 *
	 * @return string
	 */
	public static function debugXML( $node, $standalone = false ) {

		self::init();
		$node->setAttribute( 'debug', self::$enabled ? '1' : '0' );
		$node->setAttribute( 'debugConsole', self::$console );
		$node->setAttribute( 'caches', self::$caches ? '1' : '0' );
		if( !self::$console ) {
			return;
		}
		/** @var $debugNode \DOMElement */
		$debugNode = $node->appendChild( $node->ownerDocument->createElement( 'debug' ) );
		Libs\XML\DOM::array2domAttr( $debugNode, self::$output, true );
		if( $standalone ) {
			$node->setAttribute( 'standalone', 1 );
		}
	}

	private static $handledByException = null;

	/**
	 * Callback для эксцепшенов
	 *
	 * @static
	 *
	 * @param \Difra\Exception $exception
	 *
	 * @return bool
	 */
	public static function captureException( $exception ) {

		self::init();
		$err = array(
			'class' => 'errors',
			'stage' => 'exception',
			'message' => $msg = $exception->getMessage(),
			'file' => $file = $exception->getFile(),
			'line' => $line = $exception->getLine(),
			'traceback' => $exception->getTrace()
		);
		self::$handledByException = "$msg in $file:$line";
		self::addLineAsArray( $err );
		return false;
	}

	/** @var array Текст последней ошибки, пойманной captureNormal, чтобы не поймать её ещё раз в captureShutdown */
	private static $handledByNormal = null;

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

		self::$handledByNormal = $message;
		if( error_reporting() == 0 ) {
			return false;
		}
		$err = array(
			'class' => 'errors',
			'type' => $type,
			'error' => Libs\Debug\errorConstants::getInstance()->getVerbalError( $type ),
			'message' => $message,
			'file' => $file,
			'line' => $line,
			'stage' => 'normal'
		);
		$err['traceback'] = debug_backtrace();
		array_shift( $err['traceback'] );
		self::addLineAsArray( $err );
		return false;
	}

	/** @var bool */
	static public $shutdown = false;

	/**
	 * Callback для фатальных ошибок, которые не ловятся другими методами
	 */
	public static function captureShutdown() {

		if( View::$rendered ) {
			return;
		}
		// произошла ошибка?
		if( !( $error = error_get_last() ) and !self::$handledByException ) {
			return;
		}
		if( $error ) {
			// сохраняем информацию об ошибке
			if( self::$handledByNormal != $error['message'] ) {
				$error['error'] = Libs\Debug\errorConstants::getInstance()->getVerbalError( $error['type'] );
				$error['class'] = 'errors';
				$error['traceback'] = debug_backtrace();
				array_shift( $error['traceback'] );
				self::addLineAsArray( $error );
			}
		}

		self::$shutdown = true;

		// если по каким-то причинам рендер не случился, отрендерим свою страничку
		if( !View::$rendered ) {
			$ajax = Ajax::getInstance();
			if( !$ajax->isAjax ) {
				echo self::debugHTML( true );
			} else {
				echo $ajax->getResponse();
			}
			View::$rendered = true;
		}
	}

	/**
	 * Возвращает true, если в логе для консоли есть ошибки
	 *
	 * @return bool
	 */
	public static function hadError() {

		return self::$hadError;
	}

	/**
	 * Возвращает время выполнения
	 * @return float
	 */
	public static function getTimer() {

		return microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'];
	}

	public static function checkSlow() {

		if( self::$console ) {
			return;
		}
		$time = self::getTimer();
		if( $time > 1 ) {
			$output = '<pre>';
			foreach( self::$output as $line ) {
				if( !isset( $line['type'] ) ) {
					$line['type'] = null;
				};
				$output .= "{$line['timer']}\t{$line['class']}\t{$line['type']}\t{$line['message']}\n";
			}
			$date = date( 'r' );
			$server = print_r( $_SERVER, true );
			$post = print_r( $_POST, true );
			$cookie = print_r( $_COOKIE, true );
			$host = Envi::getHost();
			$uri = Envi::getUri();
			$user = Auth::getInstance()->data['email'];

			$output .= <<<MSG

Time:	$date
Host:	$host
Uri:	$uri
User:	$user

\$_SERVER:
$server

\$_POST:
$post

\$_COOKIE:
$cookie
MSG;
			$output .= '</pre>';
			Mailer::getInstance()->sendMail( 'errors@a-jam.ru', 'Slow script', print_r( $output, true ) );
		}
	}
}
