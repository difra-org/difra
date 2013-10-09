<?php

namespace Difra;

/**
 * Реализация абстрактного контроллера
 * Class Controller
 *
 * @package Difra
 */
class Controller {

	/** @var \Difra\Locales */
	public $locale;
	/** @var \Difra\Ajax */
	public $ajax;
	/** @var \Difra\Auth */
	protected $auth;

	/** @var bool */
	public $isAjaxAction = false;
	/** @var string */
	protected $method = null;
	/** @var string */
	protected $output = null;
	/** @var string */
	protected $outputType = 'text/plain';

	/** @var bool|int Кэширование страницы на стороне веб-сервера (в секундах) */
	public $cache = false;
	/** Значение по умолчанию */
	const DEFAULT_CACHE = 60;

	/** @var \DOMDocument */
	public $xml;
	/** @var \DOMElement */
	public $root;
	/** @var \DOMElement */
	public $realRoot;

	protected static $parameters = array();

	/**
	 * Вызов фабрики
	 * @return Controller|null
	 */
	public static function getInstance() {

		static $instance = null;
		if( is_null( $instance ) ) {
			$instance = \Difra\Envi\Action::getController();
		}
		return $instance;
	}

	/**
	 * Конструктор
	 */
	final public function __construct( $parameters = array() ) {

		self::$parameters = $parameters;

		// загрузка основных классов
		$this->locale = Locales::getInstance();
		$this->auth = Auth::getInstance();
		$this->ajax = Ajax::getInstance();

		// создание xml с данными
		$this->xml = new \DOMDocument;
		$this->realRoot = $this->xml->appendChild( $this->xml->createElement( 'root' ) );
		$this->root = $this->realRoot->appendChild( $this->xml->createElement( 'content' ) );

		// запуск диспатчера
		Debugger::addLine( 'Started controller dispatcher' );
		$this->dispatch();
		Debugger::addLine( 'Finished controller dispatcher' );
	}

	/**
	 * Предварительная инициализация.
	 * Имеет смысл, чтобы не выполнять дополнительные действия на 404 страницах.
	 */
	final static public function init() {

		self::getInstance();
	}

	/**
	 * Выбирает подходящий вариант action'а и запускает его
	 */
	final static public function run() {

		$controller = self::getInstance();
		$controller->chooseAction();
		if( !$controller->method ) {
			throw new Exception( 'Controller failed to choose action method' );
		}
		Debugger::addLine( 'Started action ' . \Difra\Envi\Action::$method );
		$controller->callAction();
		Debugger::addLine( 'Finished action ' . \Difra\Envi\Action::$method );
	}

	/**
	 * Выводит ответ в зависимости от типа запроса
	 */
	final static public function render() {

		$controller = self::getInstance();
		if( !empty( self::$parameters ) ) {
			$controller->putExpires( true );
			throw new \Difra\View\Exception( 404 );
		} elseif( !is_null( $controller->output ) ) {
			$controller->putExpires();
			header( 'Content-Type: ' . $controller->outputType . '; charset="utf-8"' );
			echo $controller->output;
			View::$rendered = true;
		} elseif( Debugger::isEnabled() and isset( $_GET['xml'] ) and $_GET['xml'] ) {
			if( $_GET['xml'] == '2' ) {
				$controller->fillXML();
			}
			header( 'Content-Type: text/xml; charset="utf-8"' );
			$controller->xml->formatOutput = true;
			$controller->xml->encoding = 'utf-8';
			echo rawurldecode( $controller->xml->saveXML() );
			View::$rendered = true;
		} elseif( !View::$rendered and $controller->ajax->isAjax ) {
			$controller->putExpires();
			header( 'Content-type: text/plain' ); // тут нужен application/json, но тогда опера предлагает сохранить файл
			echo( $controller->ajax->getResponse() );
			View::$rendered = true;
		} elseif( !View::$rendered ) {
			$controller->putExpires();
			try {
				View::render( $controller->xml );
			} catch( Exception $ex ) {
				if( !Debugger::isConsoleEnabled() ) {
					throw new View\Exception( 500 );
				} else {
					echo Debugger::debugHTML( true );
					die();
				}
			}
		}
	}

	/**
	 * Пустой dispatch
	 */
	public function dispatch() {
	}

	/**
	 * Выбор самого подходящего метода
	 */
	private function chooseAction() {

		$method = null;
		if( $this->ajax->isAjax and \Difra\Envi\Action::$methodAjaxAuth and $this->auth->logged ) {
			$this->isAjaxAction = true;
			$method = 'methodAjaxAuth';
		} elseif( $this->ajax->isAjax and \Difra\Envi\Action::$methodAjax ) {
			$this->isAjaxAction = true;
			$method = 'methodAjax';
		} elseif( \Difra\Envi\Action::$methodAuth and $this->auth->logged ) {
			$method = 'methodAuth';
		} elseif( \Difra\Envi\Action::$method ) {
			$method = 'method';
		} elseif( \Difra\Envi\Action::$methodAuth or \Difra\Envi\Action::$methodAjaxAuth ) {
			self::$parameters = array();
			throw new View\Exception( 401 );
		} else {
			throw new View\Exception( 404 );
		}
		$this->method = $method;
	}

	/**
	 * Проверка параметров и запуск экшена
	 */
	private function callAction() {

		$method = $this->method;
		$actionMethod = \Difra\Envi\Action::${$method};
		$actionReflection = new \ReflectionMethod( $this, $actionMethod );
		$actionParameters = $actionReflection->getParameters();

		// у выбранного метода нет параметров
		if( empty( $actionParameters ) ) {
			call_user_func( array( $this, $actionMethod ) );
			return;
		}

		// получаем имена именованных REQUEST_URI параметров
		$namedParameters = array();
		foreach( $actionParameters as $parameter ) {
			$class = $parameter->getClass() ? $parameter->getClass()->name : 'Difra\Param\NamedString';
			if( call_user_func( array( "$class", "getSource" ) ) == 'query' and call_user_func( array( "$class", "isNamed" ) )
			) {
				$namedParameters[] = $parameter->getName();
			}
		}

		// получаем значения параметров
		$callParameters = array();
		foreach( $actionParameters as $parameter ) {
			$name = $parameter->getName();
			$class = $parameter->getClass() ? $parameter->getClass()->name : 'Difra\Param\NamedString';
			switch( call_user_func( array( "$class", "getSource" ) ) ) {
				case 'query':
					// параметр из query — нужно соблюдать очередность параметров
					if( call_user_func( array( "$class", "isNamed" ) ) ) {
						// именованный параметр
						if( sizeof( self::$parameters ) >= 2 and self::$parameters[0] == $name ) {
							array_shift( self::$parameters );
							if( !call_user_func( array( "$class", 'verify' ), self::$parameters[0] ) ) {
								throw new View\Exception( 404 );
							}
							$callParameters[$parameter->getName()] =
								new $class( array_shift( self::$parameters ) );
						} elseif( !$parameter->isOptional() ) {
							throw new View\Exception( 404 );
						} else {
							$callParameters[$parameter->getName()] = null;
						}
						array_shift( $namedParameters );
					} else {
						if( !empty( self::$parameters ) and ( !$parameter->isOptional() or
								empty( $namedParameters ) or
								self::$parameters[0] != $namedParameters[0] )
						) {
							if( !call_user_func( array( "$class", 'verify' ), self::$parameters[0] ) ) {
								throw new View\Exception( 404 );
							}
							$callParameters[$name] = new $class( array_shift( self::$parameters ) );
						} elseif( !$parameter->isOptional() ) {
							throw new View\Exception( 404 );
						} else {
							$callParameters[$parameter->getName()] = null;
						}
					}
					break;
				case 'ajax':
					$value = $this->ajax->getParam( $name );
					if( !is_null( $value ) and $value !== '' ) {
						if( !call_user_func( array( "$class", "verify" ), $value ) ) {
							$this->ajax->invalid( $name );
							continue;
						}
						$callParameters[$name] = new $class( $value );
					} elseif( call_user_func( array( "$class", 'isAuto' ) ) ) {
						$callParameters[$name] = new $class;
					} elseif( !$parameter->isOptional() ) {
						$this->ajax->required( $name );
					} else {
						$callParameters[$name] = null;
					}
			}
		}
		if( !$this->ajax->hasProblem() ) {
			call_user_func_array( array( $this, $actionMethod ), $callParameters );
		}
	}

	/**
	 * Заполнение XML всевозможными данными для дальнейшего рендера шаблона
	 *
	 * @param \DOMDocument|null $xml
	 * @param null              $instance
	 */
	public function fillXML( &$xml = null, $instance = null ) {

		if( is_null( $xml ) ) {
			$xml = $this->xml;
			$node = $this->realRoot;
		} else {
			$node = $xml->documentElement;
		}
		Debugger::addLine( 'Filling XML data for render: Started' );
		$node->setAttribute( 'lang', $this->locale->locale );
		$node->setAttribute( 'site', Envi::getSite() );
		$node->setAttribute( 'host', $host = Envi::getHost() );
		$node->setAttribute( 'mainhost', $mainhost = Envi::getHost( true ) );
		$node->setAttribute( 'instance', $instance ? $instance : View::$instance );
		$node->setAttribute( 'uri', Envi::getUri() );
		$node->setAttribute( 'controllerUri', \Difra\Envi\Action::getControllerUri() );
		if( $host != $mainhost ) {
			$node->setAttribute( 'urlprefix', 'http://' . $mainhost );
		}
		// get user agent
		Envi\UserAgent::getUserAgentXML( $node );
		// ajax flag
		$node->setAttribute( 'ajax',
				     ( $this->ajax->isAjax or ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and
						     $_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage' ) ) ? '1'
					     : '0' );
		$node->setAttribute( 'switcher',
				     ( !$this->cache and isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and
					     $_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage' ) ? '1' : '0' );
		// build number
		$node->setAttribute( 'build', \Difra\Envi\Version::getBuild() );
		// date
		/** @var $dateNode \DOMElement */
		$dateNode = $node->appendChild( $xml->createElement( 'date' ) );
		$dateFields = 'deAamBbYycxHMS';
		$t = time();
		for( $i = 0; $i < strlen( $dateFields ); $i++ ) {
			$dateNode->setAttribute( $dateFields{$i}, strftime( '%' . $dateFields{$i}, $t ) );
		}
		// debug flag
		$node->setAttribute( 'debug', Debugger::isEnabled() ? '1' : '0' );
		// config values (for js variable)
		$configNode = $node->appendChild( $xml->createElement( 'config' ) );
		Envi::getConfigXML( $configNode );
		// menu
		if( $menuResource = Resourcer::getInstance( 'menu' )->compile( View::$instance ) ) {
			$menuXML = new \DOMDocument();
			$menuXML->loadXML( $menuResource );
			$node->appendChild( $xml->importNode( $menuXML->documentElement, true ) );
		}
		// auth
		$this->auth->getAuthXML( $node );
		// locale
		$this->locale->getLocaleXML( $node );
		// Добавление объекта config для js
		$config = Envi::getConfig();
		$confJS = '';
		foreach( $config as $k => $v ) {
			$confJS .= "config.{$k}='" . addslashes( $v ) . "';";
		}
		$node->setAttribute( 'jsConfig', $confJS );
		Debugger::addLine( 'Filling XML data for render: Done' );
		Debugger::debugXML( $node );
	}

	/**
	 * Функция для проверки, что URL был вызван не с «левого» хоста
	 *
	 * @throws Exception
	 */
	public function checkReferer() {

		if( empty( $_SERVER['HTTP_REFERER'] ) ) {
			throw new Exception( 'Bad referer' );
		}
		if( ( substr( $_SERVER['HTTP_REFERER'], 0, 7 ) != 'http://' ) and (
				substr( $_SERVER['HTTP_REFERER'], 0, 8 ) != 'https://' )
		) {
			throw new Exception( 'Bad referer' );
		}
		$domain = explode( '://', $_SERVER['HTTP_REFERER'], 2 );
		$domain = explode( '/', $domain[1] );
		$domain = $domain[0] . '/';
		if( false === strpos( $domain, Envi::getHost( true ) ) ) {
			throw new Exception( 'Bad referer' );
		}
	}

	/**
	 * Устанавливает заголовок X-Accel-Expires для кэширования страниц целиком на стороне веб-сервера
	 *
	 * @param bool|int $ttl
	 */
	public function putExpires( $ttl = null ) {

		if( Debugger::isEnabled() ) {
			return;
		}
		if( is_null( $ttl ) ) {
			$ttl = $this->cache;
		}
		if( $ttl === true ) {
			$ttl = self::DEFAULT_CACHE;
		}
		if( !$ttl or !is_numeric( $ttl ) or $ttl < 0 ) {
			return;
		}
		View::addExpires( $ttl );
	}
}

