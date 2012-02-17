<?php

namespace Difra;

class Action {

	public $parameters = array();
	public $uri = '';

	public $dispatchers = array();

	public $class = null;
	public $className = null;
	public $controller = null;

	public $method = null;
	public $methodAuth = null;
	public $methodAjax = null;
	public $methodAjaxAuth = null;

	public $methodTypes = array(
		array( '',	''	),
		array( '',	'Auth'	),
		array( 'Ajax',	''	),
		array( 'Ajax',	'Auth'	)
	);

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {
	}

	public function find() {

		if( $this->className ) {
			return false;
		}
		$uri = $this->getUri();
		$cacheKey = 'action:uri:' . $uri;
		if( !Debugger::getInstance()->isEnabled() and $data = Cache::getInstance()->get( $cacheKey ) ) {
			switch( $data['result'] ) {
			case 'action':
				foreach( $data['controllers'] as $cont ) {
					include_once( $cont );
				}
				foreach( $data['vars'] as $k => $v ) {
					$this->$k = $v;
				}
				new $this->class;
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
					die();
				} catch( Exception $ex ) {
					View::getInstance()->httpError( 404 );
				}
			}
		}

		// get controller path
		$path = '';
		$depth = $dirDepth = 0;
		$controllerDirs = Plugger::getInstance()->getPaths();
		$controllerDirs = array_merge( array(
							    DIR_SITE, DIR_ROOT, DIR_FW
						       ), $controllerDirs );
		foreach( $controllerDirs as $k => $v ) {
			$controllerDirs[$k] = "$v/controllers/";
		}
		$dirs = $controllerDirs;

		foreach( $parts as $part ) {
			$path .= "$part/";
			$depth++;
			$newDirs = array();
			foreach( $controllerDirs as $nextDir ) {
				if( is_dir( $nextDir . $path ) ) {
					$newDirs[] = $nextDir . $path;
					$dirDepth = $depth;
				}
			}
			if( empty( $newDirs ) ) {
				break;
			}
			$dirs = $newDirs;
		}

		$cname = '';
		$controllers = array();
		if( isset( $parts[$dirDepth] ) ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . $parts[$dirDepth] . '.php' ) ) {
					$cname = $parts[$dirDepth];
					$controllers[] = "{$tmpDir}{$cname}.php";
				}
			}
		}
		if( empty( $controllers ) ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . 'index.php' ) ) {
					$cname = 'index';
					$controllers[] = "{$tmpDir}index.php";
				}
			}
		}
		if( empty( $controllers ) ) {
			$this->saveCache( $cacheKey, array( 'result' => '404' ) );
			View::getInstance()->httpError( 404 );
		}

		// load controller and dispatchers
		$className = '';
		for( $i = 0; $i < $dirDepth; $i++ ) {
			$className .= ucfirst( $parts[$i] );
		}
		if( $cname != 'index' ) {
			$dirDepth++;
		}
		$this->class = $className = $className . ucfirst( $cname ) . 'Controller';

		$match['controllers'] = $controllers;
		foreach( $controllers as $fileName ) {
			include_once( $fileName );
		}
		if( !class_exists( $className ) ) {
			throw new exception( "Error! Controller class $className not found" );
		}
		$match['vars']['class'] = $className;

		// detect action method
		$methodName = false;
		$methodNames = isset( $parts[$dirDepth] ) ? array( $parts[$dirDepth], 'index' ) : array( 'index' );
		foreach( $methodNames as $methodTmp ) {
			foreach( $this->methodTypes as $methodType ) {
				if( method_exists( $className, $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1] ) ) {
					$methodName = $methodTmp;
					$methodVar = "method{$methodType[0]}{$methodType[1]}";
					$this->$methodVar = $match['vars'][$methodVar] = $m;
				}
			}
			if( $methodName and $methodName != 'index' ) {
				$dirDepth++;
				break;
			}
		}
		$parts = array_slice( $parts, $dirDepth );

		$this->parameters = $parts;
		$match['vars']['parameters'] = $this->parameters;
		$match['result'] = 'action';

		$this->saveCache( $cacheKey, $match );

		$this->className = $className;
	}

	public function run() {

		$this->controller = new $this->className;
	}

	private function saveCache( $key, $match ) {

		Cache::getInstance()->put( $key, $match, 300 );
	}

	public function dispatch( $plugin, $dispatcher ) {

		$plugger = Plugger::getInstance();
		if( is_null( $plugin = $plugger->getPlugin( $plugin ) ) ) {
			throw new exception( "Called dispatcher '$dispatcher' from non-existent plugin '$plugin'" );
		}
		if( !is_file( $file = "{$plugger->getPath()}/{$plugin}/dispatchers/$dispatcher" ) ) {
			throw new exception( "Dispatcher '$dispatcher' not found in plugin '$plugin'" );
		}
		include_once( $file );
		return true;
	}

	public function addDispatcher( $instance ) {

		$this->dispatchers[] = $instance;
	}

	public function runDispatchers( $controller ) {

		if( empty( $this->dispatchers ) ) {
			return false;
		}
		foreach( $this->dispatchers as $dispatcher ) {
			$dispatcher->run( $controller );
		}
		return true;
	}

	public function getUri() {

		$uri = $this->uri = $_SERVER['REQUEST_URI'];
		if( false !== strpos( $uri, '?' ) ) {
			$uri = substr( $uri, 0, strpos( $uri, '?' ) );
		}
		return trim( $uri, '/' );
	}
}
