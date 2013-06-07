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

		$uri = $this->getUri();
		$cacheKey = 'action:uri:' . $uri;
		if( $this->loadCache( $uri ) ) {
			return;
		}

		$parts = $uri ? explode( '/', $uri ) : array();
		$match = array( 'vars' => array() );

		if( $this->getResource( $parts ) ) {
			return;
		}

		// ищем директории с самой глубокой вложенностью, подходящие под запрос
		$path = '';
		$depth = $dirDepth = 0;
		$controllerDirs = $dirs = $this->getControllerPaths();
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

		// ищем файл контроллера с названием из следующей части пути
		$cname = '';
		$controller = null;
		if( isset( $parts[$dirDepth] ) ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . $parts[$dirDepth] . '.php' ) ) {
					$cname = $parts[$dirDepth];
					$controller = "{$tmpDir}{$cname}.php";
					break;
				}
			}
		}

		// если не нашли, ищем файл контроллера с названием index.php
		if( !$controller ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . 'index.php' ) ) {
					$cname = 'index';
					$controller = "{$tmpDir}index.php";
					break;
				}
			}
		}

		// файл контроллера не найден — 404
		if( !$controller ) {
			Cache::getInstance()->put( $cacheKey, $match, 300 );
			View::getInstance()->httpError( 404 );
			return;
		}

		// получаем имя класса контроллера
		$className = '';
		for( $i = 0; $i < $dirDepth; $i++ ) {
			$className .= ucfirst( $parts[$i] );
		}
		if( $cname != 'index' ) {
			$dirDepth++;
		}
		$className = $className . ucfirst( $cname ) . 'Controller';

		// подключаем контроллер
		$match['controller'] = $controller;
		$match['vars']['className'] = $className;
		include_once( $controller );
		if( !class_exists( $className ) ) {
			throw new exception( "Error! Controller class $className not found" );
		}

		// получаем имя экшена
		$foundMethod = false;
		$methodNames = isset( $parts[$dirDepth] ) ? array( $parts[$dirDepth], 'index' ) : array( 'index' );
		foreach( $methodNames as $methodTmp ) {
			foreach( $this->methodTypes as $methodType ) {
				if( method_exists( $className, $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1] ) ) {
					$foundMethod = $methodTmp;
					$methodVar = "method{$methodType[0]}{$methodType[1]}";
					$this->$methodVar = $match['vars'][$methodVar] = $m;
				}
			}
			if( $foundMethod and $foundMethod != 'index' ) {
				$dirDepth++;
				break;
			}
		}
		$parts = array_slice( $parts, $dirDepth );

		// кэшируем данные для этого uri
		$this->parameters = $parts;
		$match['vars']['parameters'] = $this->parameters;
		$match['result'] = 'action';
		Cache::getInstance()->put( $cacheKey, $match, 300 );
		$this->className = $className;
		Debugger::addLine( "Selected controller $className from $controller" );
	}

	/**
	 * Загрузка данных из кэша
	 * @param string $cacheKey
	 * @return bool
	 */
	private function loadCache( $cacheKey ) {

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
			return true;
		}
		return false;
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

		static $uri = null;
		if( !is_null( $uri ) ) {
			return $uri;
		}
		if( !empty( $_SERVER['URI'] ) ) { // это для редиректов запросов из nginx
			$this->uri = $_SERVER['URI'];
		} elseif( !empty( $_SERVER['REQUEST_URI'] ) ) {
			$this->uri = $_SERVER['REQUEST_URI'];
		} else {
			throw new Exception( 'Can\'t get URI' );
		}
		$uri = $this->uri;
		if( false !== strpos( $uri, '?' ) ) {
			$uri = substr( $uri, 0, strpos( $uri, '?' ) );
		}
		$uri = trim( $uri, '/' );
		return $uri;
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
}
