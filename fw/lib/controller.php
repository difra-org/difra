<?php

namespace Difra;

/**
 * Реализация абстрактного контроллера
 * Class Controller
 *
 * @package Difra
 */
abstract class Controller {

	/**
	 * @var \Difra\View
	 * @deprecated
	 */
	public $view;
	/** @var \Difra\Action */
	protected $action;
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
	// Значение по умолчанию
	const DEFAULT_CACHE = 60;

	/** @var \DOMDocument */
	public $xml;
	/** @var \DOMElement */
	public $root;
	/** @var \DOMElement */
	public $realRoot;

	/**
	 * Конструктор
	 */
	final public function __construct() {

		// загрузка основных классов
		$this->view = View\Old::getInstance();
		$this->locale = Locales::getInstance();
		$this->action = Action::getInstance();
		$this->auth = Auth::getInstance();
		$this->ajax = Ajax::getInstance();

		// создание xml с данными
		$this->xml = new \DOMDocument;
		$this->realRoot = $this->xml->appendChild( $this->xml->createElement( 'root' ) );
		$this->root = $this->realRoot->appendChild( $this->xml->createElement( 'content' ) );

		// запуск диспатчера
		$this->dispatch();
	}

	/**
	 * Выбирает подходящий вариант action'а и запускает его
	 */
	final public function run() {

		$this->chooseAction();
		if( !$this->method ) {
			throw new Exception( 'Controller failed to choose action method' );
		}
		Debugger::addLine( "Selected method {$this->action->method}" );
		$this->callAction();
	}

	/**
	 * Выводит ответ в зависимости от типа запроса
	 */
	final public function render() {

		if( !empty( $this->action->parameters ) ) {
			$this->putExpires( true );
			$this->view->httpError( 404 );
		} elseif( !is_null( $this->output ) ) {
			$this->putExpires();
			header( 'Content-Type: ' . $this->outputType . '; charset="utf-8"' );
			echo $this->output;
			$this->view->rendered = true;
		} elseif( Debugger::getInstance()->isEnabled() and isset( $_GET['xml'] ) and $_GET['xml'] ) {
			if( $_GET['xml'] == '2' ) {
				$this->fillXML();
			}
			header( 'Content-Type: text/xml; charset="utf-8"' );
			$this->xml->formatOutput = true;
			$this->xml->encoding = 'utf-8';
			echo rawurldecode( $this->xml->saveXML() );
			$this->view->rendered = true;
		} elseif( !$this->view->rendered and $this->ajax->isAjax ) {
			$this->putExpires();
			header( 'Content-type: text/plain' ); // тут нужен application/json, но тупая опера предлагает сохранить файл
			echo( $this->ajax->getResponse() );
			$this->view->rendered = true;
		} elseif( !$this->view->rendered ) {
			$this->putExpires();
			try {
				$this->view->render( $this->xml );
			} catch( Exception $ex ) {
				if( !Debugger::getInstance()->isConsoleEnabled() ) {
					$this->view->httpError( 500 );
				} else {
					echo Debugger::getInstance()->debugHTML( true );
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
		if( $this->ajax->isAjax and $this->action->methodAjaxAuth and $this->auth->logged ) {
			$this->isAjaxAction = true;
			$method = 'methodAjaxAuth';
		} elseif( $this->ajax->isAjax and $this->action->methodAjax ) {
			$this->isAjaxAction = true;
			$method = 'methodAjax';
		} elseif( $this->action->methodAuth and $this->auth->logged ) {
			$method = 'methodAuth';
		} elseif( $this->action->method ) {
			$method = 'method';
		} elseif( $this->action->methodAuth or $this->action->methodAjaxAuth ) {
			$this->action->parameters = array();
			$this->view->httpError( 401 );
			return;
		} else {
			$this->view->httpError( 404 );
			return;
		}
		$this->method = $method;
	}

	/**
	 * Проверка параметров и запуск экшена
	 */
	private function callAction() {

		$method = $this->method;
		$actionMethod = $this->action->$method;
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
					if( sizeof( $this->action->parameters ) >= 2 and $this->action->parameters[0] == $name ) {
						array_shift( $this->action->parameters );
						if( !call_user_func( array( "$class", 'verify' ), $this->action->parameters[0] ) ) {
							$this->view->httpError( 404 );
							return;
						}
						$callParameters[$parameter->getName()] =
							new $class( array_shift( $this->action->parameters ) );
					} elseif( !$parameter->isOptional() ) {
						$this->view->httpError( 404 );
					} else {
						$callParameters[$parameter->getName()] = null;
					}
					array_shift( $namedParameters );
				} else {
					if( !empty( $this->action->parameters ) and ( !$parameter->isOptional() or
							empty( $namedParameters ) or
							$this->action->parameters[0] != $namedParameters[0] )
					) {
						if( !call_user_func( array( "$class", 'verify' ), $this->action->parameters[0] ) ) {
							$this->view->httpError( 404 );
						}
						$callParameters[$name] = new $class( array_shift( $this->action->parameters ) );
					} elseif( !$parameter->isOptional() ) {
						$this->view->httpError( 404 );
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
	 * @param null $instance
	 */
	public function fillXML( $instance = null ) {

		Debugger::addLine( 'Filling XML data for render: Started' );
		$this->realRoot->setAttribute( 'lang', $this->locale->locale );
		$this->realRoot->setAttribute( 'controller', $this->action->className );
		$this->realRoot->setAttribute( 'action', $this->action->method );
		$this->realRoot->setAttribute( 'host', Site::getInstance()->getHost() );
		$this->realRoot->setAttribute( 'hostname', $host = Envi::getHost() );
		$this->realRoot->setAttribute( 'mainhost', $mainhost = Envi::getHost( true ) );
		$this->realRoot->setAttribute( 'instance', $instance ? $instance : $this->view->instance );
		if( $host != $mainhost ) {
			$this->realRoot->setAttribute( 'urlprefix', 'http://' . $mainhost );
		}
		// get user agent
		Envi\UserAgent::getUserAgentXML( $this->realRoot );
		// ajax flag
		$this->realRoot->setAttribute( 'ajax',
			( $this->ajax->isAjax or ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and
					$_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage' ) ) ? '1'
				: '0' );
		$this->realRoot->setAttribute( 'switcher',
			( !$this->cache and isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and
				$_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage' ) ? '1' : '0' );
		// build number
		$this->realRoot->setAttribute( 'build', Site::getInstance()->getBuild() );
		// date
		/** @var $dateNode \DOMElement */
		$dateNode = $this->realRoot->appendChild( $this->xml->createElement( 'date' ) );
		$dateFields = 'deAamBbYycxHMS';
		$t = time();
		for( $i = 0; $i < strlen( $dateFields ); $i++ ) {
			$dateNode->setAttribute( $dateFields{$i}, strftime( '%' . $dateFields{$i}, $t ) );
		}
		// debug flag
		$this->realRoot->setAttribute( 'debug', Debugger::getInstance()->isEnabled() ? '1' : '0' );
		// config values (for js variable)
		$configNode = $this->realRoot->appendChild( $this->xml->createElement( 'config' ) );
		Site::getInstance()->getConfigXML( $configNode );
		// menu
		if( $menuResource = Resourcer::getInstance( 'menu' )->compile( $this->view->instance ) ) {
			$menuXML = new \DOMDocument();
			$menuXML->loadXML( $menuResource );
			$this->realRoot->appendChild( $this->xml->importNode( $menuXML->documentElement, true ) );
		}
		// auth
		$this->auth->getAuthXML( $this->realRoot );
		// locale
		$this->locale->getLocaleXML( $this->realRoot );
		// Добавление объекта config для js
		$config = Site::getInstance()->getConfig();
		$confJS = '';
		foreach( $config as $k => $v ) {
			$confJS .= "config.{$k}='" . addslashes( $v ) . "';";
		}
		$this->realRoot->setAttribute( 'jsConfig', $confJS );
		Debugger::addLine( 'Filling XML data for render: Done' );
		Debugger::getInstance()->debugXML( $this->realRoot );
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
		if( false === strpos( $domain, Site::getInstance()->getMainhost() ) ) {
			throw new Exception( 'Bad referer' );
		}
	}

	/**
	 * Устанавливает заголовок X-Accel-Expires для кэширования страниц целиком на стороне веб-сервера
	 *
	 * @param bool|int $ttl
	 */
	public function putExpires( $ttl = null ) {

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

