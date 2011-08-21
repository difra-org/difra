<?php

namespace Difra;

abstract class Controller {

 	protected $view = null;
 	protected $action = null;
 	protected $locale = null;
 	protected $ajax = null;
	protected $auth = null;

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

		// run action method
		$this->_runAction();

		// add XML data
		$this->auth->getAuthXML( $realRoot );
		$this->locale->getLocaleXML( $realRoot );
		Menu::getInstance( $this->view->instance )->getXML( $realRoot );
		//$this->root->setAttribute( 'menuitem', Menu::getInstance()->getCurrent( $this->action->uri ) );
	}

	private function _runAction() {

		// выбор метода
		$finalMethod = false;
		if( $this->ajax->isAjax and $this->action->methodAjaxAuth and $this->auth->logged ) {
			$finalMethod = 'methodAjaxAuth';
		} elseif( $this->ajax->isAjax and $this->action->methodAjax ) {
			$finalMethod = 'methodAjax';
		} elseif( $this->action->methodAuth and $this->auth->logged ) {
			$finalMethod = 'methodAuth';
		} elseif( $this->action->method ) {
			$finalMethod = 'method';
		} elseif( $this->action->methodAuth ) {
			$this->noAuth();
			return;
		}
		// получение параметров и вызов метода
		if( $finalMethod ) {
			try {
				$callParameters = array();
				$actionMethod = $this->action->$finalMethod;
				$actionReflection = new \ReflectionMethod( $this, $actionMethod );
				$actionParameters = $actionReflection->getParameters();
				if( !empty( $actionParameters ) ) {
					foreach( $actionParameters as $parameter ) {
						if( !is_null( $val = $this->_getParameter( $parameter->getName() ) ) ) {
							$callParameters[$parameter->getName()] = $val;
						} elseif( !$parameter->isOptional() ) {
							// штатная обработка 404, никаких exception тут вызывать не нужно!
							$this->view->httpError( 404 );
							return;
						}
					}
				}
				call_user_func_array( array( $this, $actionMethod ), $callParameters );
			} catch( Exception $e ) {
				throw new Exception( 'Problem calling action.' );
			}
		}
	}

	private function _getParameter( $name ) {

		if( empty( $this->action->parameters ) ) {
			return null;
		}
		while( list( $key, $parameter ) = each( $this->action->parameters ) ) {
			if( $parameter == $name ) {
				list( $key2, $parameter2 ) = each( $this->action->parameters );
				unset( $this->action->parameters[$key2] );
				unset( $this->action->parameters[$key] );
				$this->action->parameters = array_values( $this->action->parameters );
				return $parameter2;
			}
		}
		return null;
	}

	final public function __destruct() {

		if( !empty( $this->action->parameters ) ) {
			return $this->view->httpError( 404 );
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
		$this->root->setAttribute( 'build', Site::getInstance()->getBuild() );
		$configNode = $this->root->appendChild( $this->xml->createElement( 'config' ) );
		//Site::getInstance()->getLocalesListXML( $configNode );
	}

	public function noAuth() {

		$this->action->parameters = array();
		return $this->view->httpError( 401 );
	}
	
	public function getPage() {
		
		if( empty( $this->action->parameters ) ) {
			return 1;
		}
		while( list( $key, $parameter ) = each( $this->action->parameters ) ) {
			if( $parameter == 'page' ) {
				list( $key2, $parameter2 ) = each( $this->action->parameters );
				if( ctype_digit( $parameter2 ) ) {
					unset( $this->action->parameters[$key2] );
					unset( $this->action->parameters[$key] );
					$this->action->parameters = array_values( $this->action->parameters );
					return $parameter2;
				}
			}
		}
		return 1;
	}
}

