<?php

namespace Difra\Envi;

use Difra\Envi, Difra\Debugger, Difra\View, Difra\Cache, Difra\Resourcer, Difra\Plugger;

/**
 * Определение нужного контроллера и метода (экшена), запуск выполнения контроллера
 * Class Action
 *
 * @package Difra
 */
class Action {

	/** @var string[] */
	public static $parameters = array();

	/** @var string */
	public static $className = null;
	/** @var \Difra\Controller */
	public static $controller = null;

	/** @var string */
	public static $method = null;
	/** @var string */
	public static $methodAuth = null;
	/** @var string */
	public static $methodAjax = null;
	/** @var string */
	public static $methodAjaxAuth = null;
	/** @var array */
	public static $methodTypes = array(
		array( '', '' ),
		array( '', 'Auth' ),
		array( 'Ajax', '' ),
		array( 'Ajax', 'Auth' )
	);

	/**
	 * Ищет контроллер и action для текущего URI (запускается из события)
	 *
	 * @throws \Difra\Exception
	 */
	public static function find() {

		if( self::loadCache() ) {
			return;
		}

		$uri = trim( Envi::getUri(), '/' );
		$parts = $uri ? explode( '/', $uri ) : array();

		if( self::getResource( $parts ) ) {
			return;
		}

		if( !$controllerFilename = self::findController( $parts ) ) {
			self::saveCache( '404' );
			throw new View\Exception( 404 );
		}

		/** @noinspection PhpIncludeInspection */
		include_once( $controllerFilename );
		if( !class_exists( self::$className ) ) {
			throw new \Difra\Exception( 'Error! Controller class ' . self::$className . ' not found' );
		}

		self::findAction( $parts );
		self::$parameters = $parts;

		self::saveCache( 'action' );
		Debugger::addLine( 'Selected controller ' . self::$className . " from $controllerFilename" );
	}

	/**
	 * Загрузка данных из кэша
	 * @throws View\Exception
	 * @return bool
	 */
	private static function loadCache() {

		if( !Debugger::isEnabled() and $data = Cache::getInstance()->get( self::getCacheKey() ) ) {
			switch( $data['result'] ) {
			case 'action':
				/** @noinspection PhpIncludeInspection */
				include_once( $data['controller'] );
				foreach( $data['vars'] as $k => $v ) {
					self::${$k} = $v;
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
	private static function saveCache( $result = 'action' ) {

		if( $result != '404' ) {
			$match = array(
				'vars' => array(
					'className' => self::$className,
					'parameters' => self::$parameters,
				),
				'result' => $result
			);
			foreach( self::$methodTypes as $methodType ) {
				$methodVar = "method{$methodType[0]}{$methodType[1]}";
				$match['vars'][$methodVar] = self::${$methodVar};
			}
		} else {
			$match = array(
				'result' => '404'
			);
		}

		Cache::getInstance()->put( self::getCacheKey(), $match, 300 );
	}

	/**
	 * Обработка запросов к ресурсам
	 * @param string[] $parts
	 * @throws View\Exception
	 * @return bool
	 */
	private static function getResource( $parts ) {

		if( sizeof( $parts ) == 2 ) {
			$resourcer = Resourcer::getInstance( $parts[0], true );
			if( $resourcer and $resourcer->isPrintable() ) {
				try {
					if( !$resourcer->view( $parts[1] ) ) {
						throw new View\Exception( 404 );
					}
					View::$rendered = true;
					die();
				} catch( \Difra\Exception $ex ) {
					throw new View\Exception( 404 );
				}
			}
		}
		return false;
	}

	/**
	 * Запускает исполнение логики контроллера (вызывается из события)
	 */
	public static function run() {

		self::$controller = new self::$className;
		self::$controller->run();
	}

	/**
	 * Вызов render() из контроллера
	 */
	public static function render() {

		self::$controller->render();
	}

	/**
	 * Собирает пути к папкам всех контроллеров
	 * @return string[]
	 */
	public static function getControllerPaths() {

		static $controllerDirs = null;
		if( !is_null( $controllerDirs ) ) {
			return $controllerDirs;
		}
		$controllerDirs = Plugger::getPaths();
		$controllerDirs = array_merge( array( DIR_SITE, DIR_ROOT, DIR_FW ), $controllerDirs );
		foreach( $controllerDirs as $k => $v ) {
			$controllerDirs[$k] = $v . 'controllers/';
		}
		return $controllerDirs;
	}

	/**
	 * @return string
	 */
	private static function getCacheKey() {

		return 'action:uri:' . Envi::getUri();
	}

	/**
	 * Поиск папок с самой глубокой вложенностью, подходящих для запроса
	 * @param string[] $parts
	 * @return string[]
	 */
	private static function findControllerDirs( &$parts ) {

		$path = '';
		$depth = 0;
		$controllerDirs = $dirs = self::getControllerPaths();
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
		self::$className = array_slice( $parts, 0, $depth );
		$parts = array_slice( $parts, $depth );
		return $dirs;
	}

	/**
	 * Поиск подходящего контроллера
	 * @param $parts
	 * @return null|string
	 */
	private static function findController( &$parts ) {

		$dirs = self::findControllerDirs( $parts );
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
		self::$className[] = $cname;
		foreach( self::$className as $k => $v ) {
			self::$className[$k] = ucFirst( $v );
		};
		self::$className = implode( self::$className ) . 'Controller';
		return $controllerFile;
	}

	/**
	 * Поиск подходящих экшенов
	 * @param string[] $parts
	 * @return bool|string
	 */
	private static function findAction( &$parts ) {

		$foundMethod = false;
		$methodNames = !empty( $parts ) ? array( $parts[0], 'index' ) : array( 'index' );
		foreach( $methodNames as $methodTmp ) {
			foreach( self::$methodTypes as $methodType ) {
				if( method_exists( self::$className, $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1] ) ) {
					$foundMethod = $methodTmp;
					$methodVar = "method{$methodType[0]}{$methodType[1]}";
					self::${$methodVar} = $m;
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
