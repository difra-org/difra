<?php

abstract class Controller {

 	protected $view = null;
 	protected $action = null;
 	protected $locale = null;
 	protected $ajax = null;
	protected $auth = null;

 	protected $output = null;
 	public $xml;
 	public $root;
	
	final static function getInstance( $action ) {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self( $action );
	}

	final public function __construct() {

		// load essentials
		$this->view	= View::getInstance();
		$this->locale	= Site::getInstance()->getLocaleObj();
		$this->action	= Action::getInstance();
		$this->auth	= Auth::getInstance();
		$this->ajax	= Ajax::getInstance();

		$this->_initXML();

		// run dispatchers
		if( method_exists( $this, 'dispatch' ) ) {
			$this->dispatch();
		}
		Plugger::getInstance()->runDispatchers( $this );
		$this->action->runDispatchers( $this );

		// run action method
		$this->_runAction();

		// add XML data
		$this->auth->getAuthXML( $this->root );
		$this->locale->getLocaleXML( $this->root );
		Menu::getInstance( $this->view->instance )->getXML( $this->root );
		//$this->root->setAttribute( 'menuitem', Menu::getInstance()->getCurrent( $this->action->uri ) );
	}

	private function _runAction() {

		if( $this->ajax->isAjax and $this->action->methodAjaxAuth and $this->auth->logged ) {
			$this->{$this->action->methodAjaxAuth}();
		} elseif( $this->ajax->isAjax and $this->action->methodAjax ) {
			$this->{$this->action->methodAjax}();
		} elseif( $this->action->methodAuth and $this->auth->logged ) {
			$this->{$this->action->methodAuth}();
		} elseif( $this->action->method ) {
			$this->{$this->action->method}();
		} elseif( $this->action->methodAuth ) {
			$this->noAuth();
		}
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
			echo( $this->ajax->getResponse() );

		} else {
			if( !$this->view->rendered ) {
				$this->view->render( $this->xml );
			}
		}
	}

	private function _initXML() {

		$this->xml = new DOMDocument;
		$this->root = $this->xml->appendChild( $this->xml->createElement( 'root' ) );
		$this->root->setAttribute( 'lang', $this->locale->locale );
		$this->root->setAttribute( 'controller', $this->action->class );
		$this->root->setAttribute( 'action', $this->action->method );
		$this->root->setAttribute( 'host', Site::getInstance()->getHost() );
		$configNode = $this->root->appendChild( $this->xml->createElement( 'config' ) );
		//Site::getInstance()->getLocalesListXML( $configNode );
	}

	public function noAuth() {

		return $this->view->httpError( 401 );
	}
}

