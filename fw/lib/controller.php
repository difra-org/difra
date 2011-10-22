<?php

namespace Difra;

abstract class Controller {

 	protected $view = null;
 	protected $action = null;
 	protected $locale = null;
 	protected $ajax = null;
	protected $auth = null;
	protected $method = null;

 	protected $output = null;
	
	/**
	 * @var \DOMDocument
	 */
 	public $xml;
	/**
	 * @var \DOMElement
	 */
 	public $root;
	
	public static function getInstance( $action ) {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self( $action );
	}

	public function __construct() {

		// load essentials
		$this->view	= View::getInstance();
		$this->locale	= Site::getInstance()->getLocaleObj();
		$this->action	= Action::getInstance();
		$this->auth	= Auth::getInstance();
		$this->ajax	= Ajax::getInstance();

		$this->_initXML();
		$realRoot = $this->root;

		// run dispatchers
		if( method_exists( $this, 'dispatch' ) ) {
			$this->dispatch();
		}
		Plugger::getInstance()->runDispatchers( $this );
		$this->action->runDispatchers( $this );

		// add XML data
		$this->auth->getAuthXML( $realRoot );
		$this->locale->getLocaleXML( $realRoot );
		Menu::getAllXML( $this->view->instance, $realRoot );
		//$this->root->setAttribute( 'menuitem', Menu::getInstance()->getCurrent( $this->action->uri ) );

		// run action method
		$this->_runAction();
	}

	private function _runAction() {

		if( $this->method = $this->_chooseMehod() ) {
			$this->_callMethod( $this->method );
		}
	}

	/**
	 * Выбор самого подходящего метода
	 * @return null|string
	 */
	private function _chooseMehod() {

		$finalMethod = null;
		if( $this->ajax->isAjax and $this->action->methodAjaxAuth and $this->auth->logged ) {
			$finalMethod = 'methodAjaxAuth';
		} elseif( $this->ajax->isAjax and $this->action->methodAjax ) {
			$finalMethod = 'methodAjax';
		} elseif( $this->action->methodAuth and $this->auth->logged ) {
			$finalMethod = 'methodAuth';
		} elseif( $this->action->method ) {
			$finalMethod = 'method';
		} elseif( $this->action->methodAuth or $this->action->methodAjaxAuth ) {
			$this->action->parameters = array();
			$this->view->httpError( 401 );
			return null;
		} else {
			$this->view->httpError( 404 );
			return null;
		}
		return $finalMethod;
	}

	private function _callMethod( $method ) {

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
				if( call_user_func( array( "$class", "isNamed" )) ) {
					// именованный параметр
					if( sizeof( $this->action->parameters ) >= 2 and $this->action->parameters[0] == $name ) {
						array_shift( $this->action->parameters );
						if( !call_user_func( array( "$class", 'verify' ), $this->action->parameters[0] ) ) {
							$this->view->httpError( 404 );
							return;
						}
						$callParameters[$parameter->getName()] = new $class( array_shift( $this->action->parameters ) );
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
						if( !call_user_func( array( "$class", 'verify'), $this->action->parameters[0] ) ) {
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
				if( $value = $this->ajax->getParam( $name ) ) {
					if( !call_user_func( array( "$class", "verify" ), $value ) ) {
						$this->ajax->invalid( $name );
						continue;
					}
					$callParameters[$name] = new $class( $value );
				} elseif( call_user_func( array( "$class", 'isAuto' ) ) ) {
					$callParameters[$name] = new $class;
				} elseif( !$parameter->isOptional() ) {
					$this->ajax->required( $name );
				}
			}
		}
		if( !$this->ajax->hasProblem() ) {
			call_user_func_array( array( $this, $actionMethod ), $callParameters );
		}
	}

	final public function __destruct() {

		if( !empty( $this->action->parameters ) ) {
			$this->view->httpError( 404 );
			return;
		}
		if( !is_null( $this->output ) ) {
			echo $this->output;
			return;
		}
		if( Debugger::getInstance()->isEnabled() and isset( $_GET['xml'] ) and $_GET['xml'] ) {
			header( 'Content-Type: text/plain' );
			$this->xml->formatOutput = true;
			$this->xml->encoding = 'utf-8';
			echo( rawurldecode( $this->xml->saveXML() ) );
		} elseif( $this->ajax->isAjax and $response = $this->ajax->getResponse() ) {
			header( 'Content-type: text/javascript' );
			echo( $response );

		} else {
			if( !$this->view->rendered ) {
				$this->view->render( $this->xml );
			}
		}
	}

	private function _initXML() {

		$this->xml = new \DOMDocument;
		$this->root = $this->xml->appendChild( $this->xml->createElement( 'root' ) );
		$this->root->setAttribute( 'lang', $this->locale->locale );
		$this->root->setAttribute( 'controller', $this->action->class );
		$this->root->setAttribute( 'action', $this->action->method );
		$this->root->setAttribute( 'host', Site::getInstance()->getHost() );
		$this->root->setAttribute( 'hostname', Site::getInstance()->getHostname() );
		$this->root->setAttribute( 'mainhost', Site::getInstance()->getMainhost() );
		if( Site::getInstance()->getHostname() != Site::getInstance()->getMainhost() ) {
			$this->root->setAttribute( 'urlprefix', 'http://' . Site::getInstance()->getMainhost() );
		}
		$this->root->setAttribute( 'ajax', ( $this->ajax->isAjax or ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and
									      $_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage' ) ) ? 1
							 : 0 );
		$this->root->setAttribute( 'build', Site::getInstance()->getBuild() );
		$configNode = $this->root->appendChild( $this->xml->createElement( 'config' ) );
		Site::getInstance()->getConfigXML( $configNode );
	}

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
}

