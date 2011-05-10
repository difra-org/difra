<?php

class Action {

	public $parameters = array();
	public $uri = '';

	public $dispatchers = array();

	public $class = null;

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

	public function run() {

		$uri = $this->_getUri();
		$parts = $uri ? explode( '/', $uri ) : array();
		
		// is it a resourcer request?
		if( sizeof( $parts ) == 2 ) {
			$resourcer = Resourcer::getInstance( $parts[0], true );
			if( $resourcer and $resourcer->isPrintable() ) {
				$resourcer->view( $parts[1] );
				return true;
			}
		}

		// get controller path
		$path = '';
		$depth = $dirDepth = 0;
		$controllerDirs = Plugger::getInstance()->getControllerDirs();
		$dirs = $controllerDirs = array_merge( $controllerDirs, array(
			DIR_SITE . 'controllers/',
			DIR_FW . 'controllers/'
		) );
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
		
		// get controller file name
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
			// TODO: check additional plugin paths here... hmmm... or not. but static pages should be cached and checked somewhere
			return View::getInstance()->httpError( 404 );
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

		foreach( $controllers as $fileName ) {
			include_once( $fileName );
		}
		if( !class_exists( $className ) ) {
			throw new exception( "Error! Controller class $className not found" );
			return View::getInstance()->httpError( 404 );
		}

		// detect action method
		$methodName = false;
		$methodNames = isset( $parts[$dirDepth] ) ? array( $parts[$dirDepth], 'index' ) : array( 'index' );
		foreach( $methodNames as $methodTmp ) {
			foreach( $this->methodTypes as $methodType ) {
				if( method_exists( $className, $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1] ) ) {
					$methodName = $methodTmp;
					$methodVar = "method{$methodType[0]}{$methodType[1]}";
					$this->$methodVar = $m;
				}
			}
			if( $methodName and $methodName != 'index' ) {
				$dirDepth++;
				break;
			}
		}
		$parts = array_slice( $parts, $dirDepth );

		$this->parameters = $parts;
		new $className();
	}

	public function dispatch( $plugin, $dispatcher ) {

		$plugger = Plugger::getInstance();
		if( !isset( $plugger->plugins[$plugin] ) ) {
			throw new exception( "Called dispatcher '$dispatcher' from non-existent plugin '$plugin'" );
			return false;
		}
		if( !is_file( $file = "{$plugger->path}/{$plugin}/dispatchers/$dispatcher" ) ) {
			echo "Not found $file";
			throw new exception( "Dispatcher '$dispatcher' not found in plugin '$plugin'" );
			return false;
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

	private function _getUri() {

		$uri = $this->uri = $_SERVER['REQUEST_URI'];
		if( false !== strpos( $uri, '?' ) ) {
			$uri = substr( $uri, 0, strpos( $uri, '?' ) );
		}
		return trim( $uri, '/' );
	}
}

