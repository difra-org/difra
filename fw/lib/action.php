<?php

namespace Difra;

class Action {

	/** @var string[] */
	public $parameters = array();
	/** @var string */
	public $uri = '';
	/** @var string */
	public $className = null;
	/** @var Controller */
	public $controller = null;
	/** @var string */
	public $method = null;
	/** @var string */
	public $methodAuth = null;
	/** @var string */
	public $methodAjax = null;
	/** @var string */
	public $methodAjaxAuth = null;
	/** @var array */
	public $methodTypes = array(
		array( '', '' ),
		array( '', 'Auth' ),
		array( 'Ajax', '' ),
		array( 'Ajax', 'Auth' )
	);

	/**
	 * Синглтон
	 * @static
	 * @return Action
	 */
	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Ищет контроллер и action для текущего URI (запускается из события)
	 *
	 * @throws exception
	 */
	public function find() {

		if( $this->className ) {
			return;
		}

		// try to load cached data for this url
		$uri      = $this->getUri();
		$cacheKey = 'action:uri:' . $uri;
		if( !Debugger::getInstance()->isEnabled() and $data = Cache::getInstance()->get( $cacheKey ) ) {
			switch( $data['result'] ) {
			case 'action':
				include_once( $data['controller'] );
				foreach( $data['vars'] as $k => $v ) {
					$this->$k = $v;
				}
				break;
			case '404':
				View::getInstance()->httpError( 404 );
			}
			return;
		}
		$parts = $uri ? explode( '/', $uri ) : array();
		$match = array( 'vars' => array() );

		// is it a resourcer request?
		if( sizeof( $parts ) == 2 ) {
			$resourcer = Resourcer::getInstance( $parts[0], true );
			if( $resourcer and $resourcer->isPrintable() ) {
				try {
					$resourcer->view( $parts[1] );
					View::getInstance()->rendered = true;
					die();
				} catch( Exception $ex ) {
					View::getInstance()->httpError( 404 );
				}
			}
		}

		// get possible controller dirs
		$path  = '';
		$depth = $dirDepth = 0;

		$controllerDirs = $dirs = $this->getControllerPaths();
		foreach( $parts as $part ) {
			$path .= "$part/";
			$depth++;
			$newDirs = array();
			foreach( $controllerDirs as $nextDir ) {
				if( is_dir( $nextDir . $path ) ) {
					$newDirs[] = $nextDir . $path;
					$dirDepth  = $depth;
				}
			}
			if( empty( $newDirs ) ) {
				break;
			}
			$dirs = $newDirs;
		}

		// find controller
		$cname      = '';
		$controller = null;
		if( isset( $parts[$dirDepth] ) ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . $parts[$dirDepth] . '.php' ) ) {
					$cname      = $parts[$dirDepth];
					$controller = "{$tmpDir}{$cname}.php";
					break;
				}
			}
		}
		if( !$controller ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . 'index.php' ) ) {
					$cname      = 'index';
					$controller = "{$tmpDir}index.php";
					break;
				}
			}
		}

		// 404
		if( !$controller ) {
			Cache::getInstance()->put( $cacheKey, $match, 300 );
			View::getInstance()->httpError( 404 );
			return;
		}

		// assemble controller class name
		$className = '';
		for( $i = 0; $i < $dirDepth; $i++ ) {
			$className .= ucfirst( $parts[$i] );
		}
		if( $cname != 'index' ) {
			$dirDepth++;
		}
		$className = $className . ucfirst( $cname ) . 'Controller';

		// include controller
		$match['controller']        = $controller;
		$match['vars']['className'] = $className;
		include_once( $controller );
		if( !class_exists( $className ) ) {
			throw new exception( "Error! Controller class $className not found" );
		}

		// detect action method
		$methodName  = false;
		$methodNames = isset( $parts[$dirDepth] ) ? array( $parts[$dirDepth], 'index' ) : array( 'index' );
		foreach( $methodNames as $methodTmp ) {
			foreach( $this->methodTypes as $methodType ) {
				if( method_exists( $className, $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1] ) ) {
					$methodName       = $methodTmp;
					$methodVar        = "method{$methodType[0]}{$methodType[1]}";
					$this->$methodVar = $match['vars'][$methodVar] = $m;
				}
			}
			if( $methodName and $methodName != 'index' ) {
				$dirDepth++;
				break;
			}
		}
		$parts = array_slice( $parts, $dirDepth );

		// cache data for this url
		$this->parameters            = $parts;
		$match['vars']['parameters'] = $this->parameters;
		$match['result']             = 'action';
		Cache::getInstance()->put( $cacheKey, $match, 300 );
		$this->className = $className;
		Debugger::addLine( "Selected controller $className from $controller" );
	}

	/**
	 * Запускает исполнение логики контроллера (вызывается из события)
	 */
	public function run() {

		$this->controller = new $this->className;
		$this->controller->run();
	}

	/**
	 * Возвращает текущий URI
	 */
	public function getUri() {

		static $uri = null;
		if( !is_null( $uri ) ) {
			return $uri;
		}
		if( !empty( $_SERVER['URI'] ) ) {
			$this->uri = $_SERVER['URI'];
		} elseif( !empty( $_SERVER['REQUEST_URI'] ) ) {
			$this->uri = $_SERVER['REQUEST_URI'];
		} else {
			die( 'Can\'t get URI' );
		}
		$uri = $this->uri;
		if( false !== strpos( $uri, '?' ) ) {
			$uri = substr( $uri, 0, strpos( $uri, '?' ) );
		}
		$uri = trim( $uri, '/' );
		return $uri;
	}

	public function render() {

		$this->controller->render();
	}

	/**
	 * Собирает пути к папкам всех контроллеров
	 * @return string[]
	 */
	public function getControllerPaths() {

		static $controllerDirs = null;
		if( !is_null( $controllerDirs ) ) {
			return $controllerDirs;
		}
		$controllerDirs = Plugger::getInstance()->getPaths();
		$controllerDirs = array_merge( array( DIR_SITE, DIR_ROOT, DIR_FW ), $controllerDirs );
		foreach( $controllerDirs as $k => $v ) {
			$controllerDirs[$k] = $v . '/controllers/';
		}
		return $controllerDirs;
	}
}
