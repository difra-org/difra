<?php

namespace Difra;

/**
 * Class Debugger
 *
 * @package Difra
 */
class Debugger {

	private $enabled = false;
	/** @var int        0 — консоль выключена, 1 — консоль включена, но не активна, 2 — консоль активна */
	private $console = 0;
	private $cacheResources = true;
	private static $output = array();
	private $hadError = false;

	/**
	 * Синглтон
	 * @return Debugger
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Конструктор
	 */
	public function __construct() {

		if( Site::getMode() != 'web' ) {
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
				( empty( $_SERVER['REQUEST_METHOD'] ) or Ajax::getInstance()->isAjax ) ? 'Off' : 'On' );
			$this->enabled = 0;
			$this->console = 0;
			return;
		}
		if( ( isset( $_GET['debug'] ) and !$_GET['debug'] ) or ( isset( $_COOKIE['debug'] ) and !$_COOKIE['debug'] ) ) {
			$this->enabled = false;
		} else {
			$this->enabled = true;
		}
		// консоль отключена?
		if( !$this->enabled or ( isset( $_COOKIE['debugConsole'] ) and !$_COOKIE['debugConsole'] ) ) {
			$this->console = 1; // консоль есть, но отлов ошибок отключен
		} else {
			$this->console = 2; // консоль включена
		}

		$this->cacheResources = false;

		if( $this->console == 2 ) {
			// консоль активна — перехватываем ошибки
			ini_set( 'display_errors', 'Off' );
			ini_set( 'html_errors', 'Off' );
			ini_set( 'error_reporting', E_ALL );
			set_error_handler( array( '\Difra\Debugger', 'captureNormal' ) );
			set_exception_handler( array( '\Difra\Debugger', 'captureException' ) );
			register_shutdown_function( array( '\Difra\Debugger', 'captureShutdown' ) );
			$this->console = 2;
		} else {
			// консоль не активна — выводим ошибки
			ini_set( 'display_errors', 'On' );
			ini_set( 'error_reporting', E_ALL );
			ini_set( 'html_errors',
				( empty( $_SERVER['REQUEST_METHOD'] ) or Ajax::getInstance()->isAjax ) ? 'Off' : 'On' );
		}
	}

	/**
	 * Включен ли режим отладки
	 * @return bool
	 */
	public function isEnabled() {

		return $this->enabled;
	}

	/**
	 * Включена ли отладочная консоль?
	 * 0 — отладка полностью отключена
	 * 1 — отключен отлов ошибок
	 * 2 — консоль включена
	 * @return int
	 */
	public function isConsoleEnabled() {

		return $this->console;
	}

	/**
	 * Нужно ли кэшировать ресурсы? (js, css, xslt и т.д.)
	 *
	 * @return bool
	 */
	public function isResourceCache() {

		return $this->cacheResources;
	}

	/**
	 * Добавляет сообщение в лог для консоли
	 * @param string $line
	 */
	static function addLine( $line ) {

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
	static function addEventLine( $line ) {

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
	public function addDBLine( $type, $line ) {

		if( !$this->enabled ) {
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
	public function addLineAsArray( $array ) {

		if( !$this->enabled ) {
			return;
		}
		if( $array['class'] == 'errors' ) {
			$this->hadError = true;
		}
		$array['timer'] = self::getTimer();
		self::$output[] = $array;
	}

	/**
	 * Рендер HTML отладочной консоли
	 *
	 * @param bool $standalone      Консоль выводится на отдельной странице (если произошла фатальная ошибка и запрошенная страница
	 *                                 не может быть отрендерена)
	 * @return string
	 */
	public function debugHTML( $standalone = false ) {

		static $alreadyDidIt = false;
		if( $alreadyDidIt ) {
			return '';
		}
		/** @var $root \DOMElement */
		$xml = new \DOMDocument();
		$root = $xml->appendChild( $xml->createElement( 'root' ) );
		$this->debugXML( $root, $standalone );

		return View::getInstance()->render( $xml, 'all', true );
	}

	/**
	 * Добавляет в XML-ноду данные о дебаге и для дебаг-панели
	 *
	 * @param \DOMNode|\DOMElement $node
	 * @param bool                 $standalone
	 * @return string
	 */
	public function debugXML( $node, $standalone = false ) {

		$node->setAttribute( 'debug', $this->enabled ? '1' : '0' );
		$node->setAttribute( 'debugConsole', $this->console );
		if( !$this->console ) {
			return;
		}
		/** @var $debugNode \DOMElement */
		$debugNode = $node->appendChild( $node->ownerDocument->createElement( 'debug' ) );
		Libs\XML\DOM::array2domAttr( $debugNode, self::$output, true );
		if( $standalone ) {
			$node->setAttribute( 'standalone', 1 );
		}
	}

	/**
	 * Callback для эксцепшенов
	 *
	 * @static
	 * @param \Difra\Exception $exception
	 * @return bool
	 */
	public static function captureException( $exception ) {

		$err = array(
			'class' => 'errors',
			'stage' => 'exception',
			'message' => $exception->getMessage(),
			'file' => $exception->getFile(),
			'line' => $exception->getLine(),
			'traceback' => $exception->getTrace()
		);
		self::getInstance()->addLineAsArray( $err );
		return false;
	}

	/** @var array Текст последней ошибки, пойманной captureNormal, чтобы не поймать её ещё раз в captureShutdown */
	private static $handledByNormal = array();

	/**
	 * Callback для ловимых ошибок
	 *
	 * @static
	 * @param $type
	 * @param $message
	 * @param $file
	 * @param $line
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
		// сохраняем информацию об ошибке
		if( self::$handledByNormal != $error['message'] ) {
			$error['error'] = Libs\Debug\errorConstants::getInstance()->getVerbalError( $error['type'] );
			$error['class'] = 'errors';
			$error['traceback'] = debug_backtrace();
			array_shift( $error['traceback'] );
			self::getInstance()->addLineAsArray( $error );
		}
		// если по каким-то причинам рендер не случился, отрендерим свою страничку
		if( !View::getInstance()->rendered ) {
			$controller = Action::getInstance()->controller;
			$ajax = $controller->ajax;
			if( !$ajax->isAjax ) {
				echo self::getInstance()->debugHTML( true );
			} else {
				echo $ajax->getResponse();
			}
			$controller->view->rendered = true;
		}
	}

	/**
	 * Возвращает true, если в логе для консоли есть ошибки
	 *
	 * @return bool
	 */
	public function hadError() {

		return $this->hadError;
	}

	/**
	 * Возвращает время выполнения
	 * @return float
	 */
	public static function getTimer() {

		return microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'];
	}

	public static function checkSlow() {

		if( Debugger::getInstance()->console ) {
			return;
		}
		$time = self::getTimer();
		if( $time > 1 ) {
			$output = '<pre>';
			foreach( self::$output as $line ) {
				$output .= "{$line['timer']}\t{$line['class']}\t{$line['type']}\t{$line['message']}\n";
			}
			$date = date( 'r' );
			$server = print_r( $_SERVER, true );
			$post = print_r( $_POST, true );
			$cookie = print_r( $_COOKIE, true );
			$host = Site::getInstance()->getHostname();
			$uri = Action::getInstance()->getUri();
			$user = Auth::getInstance()->data['email'];

			$output .= <<<MSG

Time:	$date
Host:	$host
Uri:	/$uri
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
