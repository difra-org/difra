<?php

namespace Difra;

/**
 * Определение нужного контроллера и метода (экшена), запуск выполнения контроллера
 * Class Action
 *
 * @package Difra
 */
class Action {

	/** @var string[] */
	public $parameters = array();
	/** @var string */
	public $uri = null;

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

		if( $this->loadCache() ) {
			return;
		}

		$uri = $this->getUri();
		$parts = $uri ? explode( '/', $uri ) : array();

		if( $this->getResource( $parts ) ) {
			return;
		}

		if( !$controllerFilename = $this->findController( $parts ) ) {
			$this->saveCache( '404' );
			View::getInstance()->httpError( 404 );
			return;
		}

		/** @noinspection PhpIncludeInspection */
		include_once( $controllerFilename );
		if( !class_exists( $this->className ) ) {
			throw new exception( "Error! Controller class {$this->className} not found" );
		}

		$this->findAction( $parts );
		$this->parameters = $parts;

		$this->saveCache( 'action' );
		Debugger::addLine( "Selected controller {$this->className} from $controllerFilename" );
	}

	/**
	 * Загрузка данных из кэша
	 * @return bool
	 */
	private function loadCache() {

		if( !Debugger::getInstance()->isEnabled() and $data = Cache::getInstance()->get( $this->getCacheKey() ) ) {
			switch( $data['result'] ) {
			case 'action':
				/** @noinspection PhpIncludeInspection */
				include_once( $data['controller'] );
				foreach( $data['vars'] as $k => $v ) {
					$this->$k = $v;
				}
				break;
			case '404':
				View::getInstance()->httpError( 404 );
			}
			return true;
		}
		return false;
	}

	private function saveCache( $result = 'action' ) {

		if( $result != '404' ) {
			$match = array(
				'vars' => array(
					'className' => $this->className,
					'parameters' => $this->parameters,
				),
				'result' => $result
			);
			foreach( $this->methodTypes as $methodType ) {
				$methodVar = "method{$methodType[0]}{$methodType[1]}";
				$match['vars'][$methodVar] = $this->$methodVar;
			}
		} else {
			$match = array(
				'result' => '404'
			);
		}

		Cache::getInstance()->put( $this->getCacheKey(), $match, 300 );
	}

	/**
	 * Обработка запросов к ресурсам
	 * @param string[] $parts
	 * @return bool
	 */
	private function getResource( $parts ) {

		if( sizeof( $parts ) == 2 ) {
			$resourcer = Resourcer::getInstance( $parts[0], true );
			if( $resourcer and $resourcer->isPrintable() ) {
				try {
					if( !$resourcer->view( $parts[1] ) ) {
						View::getInstance()->httpError( 404 );
					}
					View::getInstance()->rendered = true;
					die();
				} catch( Exception $ex ) {
					View::getInstance()->httpError( 404 );
				}
				return true;
			}
		}
		return false;
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

		if( !is_null( $this->uri ) ) {
			return $this->uri;
		}
		if( !empty( $_SERVER['URI'] ) ) { // это для редиректов запросов из nginx
			$this->uri = $_SERVER['URI'];
		} elseif( !empty( $_SERVER['REQUEST_URI'] ) ) {
			$this->uri = $_SERVER['REQUEST_URI'];
		} else {
			throw new Exception( 'Can\'t get URI' );
		}
		if( false !== strpos( $this->uri, '?' ) ) {
			$this->uri = substr( $this->uri, 0, strpos( $this->uri, '?' ) );
		}
		$this->uri = trim( $this->uri, '/' );
		return $this->uri;
	}

	/**
	 * Вызов render() из контроллера
	 */
	public function render() {

		$this->controller->render();
	}

	/**
	 * Собирает пути к папкам всех контроллеров
	 * @return string[]
	 */
	private function getControllerPaths() {

		static $controllerDirs = null;
		if( !is_null( $controllerDirs ) ) {
			return $controllerDirs;
		}
		$controllerDirs = Plugger::getInstance()->getPaths();
		$controllerDirs = array_merge( array( DIR_SITE, DIR_ROOT, DIR_FW ), $controllerDirs );
		foreach( $controllerDirs as $k => $v ) {
			$controllerDirs[$k] = $v . 'controllers/';
		}
		return $controllerDirs;
	}

	/**
	 * @return string
	 */
	private function getCacheKey() {

		return 'action:uri:' . $this->getUri();
	}

	/**
	 * Поиск папок с самой глубокой вложенностью, подходящих для запроса
	 * @param string[] $parts
	 * @return string[]
	 */
	private function findControllerDirs( &$parts ) {

		$path = '';
		$depth = 0;
		$controllerDirs = $dirs = $this->getControllerPaths();
		foreach( $parts as $part ) {
			$path .= "$part/";
			$depth++;
			$newDirs = array();
			foreach( $controllerDirs as $nextDir ) {
				if( is_dir( $nextDir . $path ) ) {
					$newDirs[] = $nextDir . $path;
				}
			}
			if( empty( $newDirs ) ) {
				break;
			}
			$dirs = $newDirs;
		}
		$this->className = array_slice( $parts, 0, $depth );
		$parts = array_slice( $parts, $depth );
		return $dirs;
	}

	/**
	 * Поиск подходящего контроллера
	 * @param $parts
	 * @return null|string
	 */
	private function findController( &$parts ) {

		$dirs = $this->findControllerDirs( $parts );
		$cname = $controllerFile = null;
		if( !empty( $parts ) ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . $parts[0] . '.php' ) ) {
					$cname = $parts[0];
					$controllerFile = "{$tmpDir}{$cname}.php";
					break;
				}
				if( is_file( $tmpDir . 'index.php' ) ) {
					$cname = 'index';
					$controllerFile = "{$tmpDir}index.php";
				}
			}
		}
		if( !$cname ) {
			View::getInstance()->httpError( 404 );
		}
		if( $cname != 'index' ) {
			array_shift( $parts );
		}
		$this->className[] = $cname;
		array_walk( $this->className, 'ucFirst' );
		$this->className = implode( $this->className ) . 'Controller';
		return $controllerFile;
	}

	/**
	 * Поиск подходящих экшенов
	 * @param string[] $parts
	 * @return bool|string
	 */
	private function findAction( &$parts ) {

		$foundMethod = false;
		$methodNames = !empty( $parts ) ? array( $parts[0], 'index' ) : array( 'index' );
		foreach( $methodNames as $methodTmp ) {
			foreach( $this->methodTypes as $methodType ) {
				if( method_exists( $this->className, $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1] ) ) {
					$foundMethod = $methodTmp;
					$methodVar = "method{$methodType[0]}{$methodType[1]}";
					$this->$methodVar = $m;
				}
			}
			if( $foundMethod and $foundMethod != 'index' ) {
				array_shift( $parts );
				break;
			}
		}
		return $foundMethod;
	}
}
