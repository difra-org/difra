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

		if( $this->loadCache() ) {
			return;
		}

		$uri = trim( Envi::getUri(), '/' );
		$parts = $uri ? explode( '/', $uri ) : array();

		if( $this->getResource( $parts ) ) {
			return;
		}

		if( !$controllerFilename = $this->findController( $parts ) ) {
			$this->saveCache( '404' );
			throw new View\Exception( 404 );
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
	 * @throws View\Exception
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
				throw new View\Exception( 404 );
			}
			return true;
		}
		return false;
	}

	/**
	 * Сохранить результат в кэш
	 * @param string $result Тип резултата: 'action' или '404'
	 */
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
	 * @throws View\Exception
	 * @return bool
	 */
	private function getResource( $parts ) {

		if( sizeof( $parts ) == 2 ) {
			$resourcer = Resourcer::getInstance( $parts[0], true );
			if( $resourcer and $resourcer->isPrintable() ) {
				try {
					if( !$resourcer->view( $parts[1] ) ) {
						throw new View\Exception( 404 );
					}
					View::$rendered = true;
					die();
				} catch( Exception $ex ) {
					throw new View\Exception( 404 );
				}
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
	 * Вызов render() из контроллера
	 */
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
			$controllerDirs[$k] = $v . 'controllers/';
		}
		return $controllerDirs;
	}

	/**
	 * @return string
	 */
	private function getCacheKey() {

		return 'action:uri:' . Envi::getUri();
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
			$newDirs = array();
			foreach( $controllerDirs as $nextDir ) {
				if( is_dir( $nextDir . $path ) ) {
					$newDirs[] = $nextDir . $path;
				}
			}
			if( empty( $newDirs ) ) {
				break;
			}
			$depth++;
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
			}
		}
		if( !$cname ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . 'index.php' ) ) {
					$cname = 'index';
					$controllerFile = "{$tmpDir}index.php";
					break;
				}
			}
		}
		if( !$cname ) {
			return null;
		}
		if( $cname != 'index' ) {
			array_shift( $parts );
		}
		$this->className[] = $cname;
		foreach( $this->className as $k => $v ) {
			$this->className[$k] = ucFirst( $v );
		};
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
