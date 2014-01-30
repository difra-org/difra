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
	private static $parameters = array();

	/** @var string */
	private static $controllerClass = null;
	/** @var string */
	private static $controllerFile = '';

	/** @var \Difra\Controller */
	private static $controllerObj = null;

	/** @var string */
	private static $controllerUri = null;

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
			Debugger::addLine( 'Cached controller ' . self::$controllerClass . ' from ' . self::$controllerFile );
			return;
		}

		$uri = trim( Envi::getUri(), '/' );
		$parts = $uri ? explode( '/', $uri ) : array();

		self::getResource( $parts );

		$controllerUriParts = $parts;
		if( !self::$controllerFile = self::findController( $parts ) ) {
			self::saveCache( '404' );
			throw new View\Exception( 404 );
		}
		self::$controllerUri = '/' . implode( '/', sizeof( $parts ) ? array_slice( $controllerUriParts, 0, -sizeof( $parts ) ) : $controllerUriParts );

		/** @noinspection PhpIncludeInspection */
		include_once( self::$controllerFile );
		if( !class_exists( self::$controllerClass ) ) {
			throw new \Difra\Exception( 'Error! Controller class ' . self::$controllerClass . ' not found' );
		}

		self::findAction( $parts );
		self::$parameters = $parts;

		self::saveCache( 'action' );
		Debugger::addLine( 'Selected controller ' . self::$controllerClass . ' from ' . self::$controllerFile );
	}

	/**
	 * Загрузка данных из кэша
	 * @throws View\Exception
	 * @return bool
	 */
	private static function loadCache() {

		if( !$data = Cache::getInstance()->get( self::getCacheKey() ) ) {
			return false;
		}
		switch( $data['result'] ) {
			case 'action':
				/** @noinspection PhpIncludeInspection */
				foreach( $data['vars'] as $k => $v ) {
					self::${$k} = $v;
				}
				include_once( self::$controllerFile );
				break;
			case '404':
				throw new View\Exception( 404 );
		}
		return true;
	}

	/**
	 * Сохранить результат в кэш
	 * @param string $result Тип резултата: 'action' или '404'
	 */
	private static function saveCache( $result = 'action' ) {

		if( $result != '404' ) {
			// save main variables
			$match = array(
				'vars' => array(
					'controllerClass' => self::$controllerClass,
					'controllerFile' => self::$controllerFile,
					'controllerUri' => self::$controllerUri,
					'parameters' => self::$parameters
				),
				'result' => $result
			);
			// save action types variables
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
	 *
	 * @throws View\Exception
	 * @return bool
	 */
	private static function getResource( $parts ) {

		if( sizeof( $parts ) != 2 ) {
			return false;
		}
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
		return false;
	}

	/**
	 * Запускает исполнение логики контроллера (вызывается из события)
	 */
	public static function getController() {

		if( !self::$controllerClass ) {
			self::find();
		}
		if( !self::$controllerObj ) {
			self::$controllerObj = new self::$controllerClass( self::$parameters );
		}
		return self::$controllerObj;
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
	 *
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
		self::$controllerClass = array_slice( $parts, 0, $depth );
		$parts = array_slice( $parts, $depth );
		return $dirs;
	}

	/**
	 * Поиск подходящего контроллера
	 * @param $parts
	 *
	 * @return null|string
	 */
	private static function findController( &$parts ) {

		$dirs = self::findControllerDirs( $parts );
		$cName = $controllerFile = null;
		if( !empty( $parts ) ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . $parts[0] . '.php' ) ) {
					$cName = $parts[0];
					$controllerFile = "{$tmpDir}{$cName}.php";
					break;
				}
			}
		}
		if( !$cName ) {
			foreach( $dirs as $tmpDir ) {
				if( is_file( $tmpDir . 'index.php' ) ) {
					$cName = 'index';
					$controllerFile = "{$tmpDir}index.php";
					break;
				}
			}
		}
		if( !$cName ) {
			return null;
		}
		if( $cName != 'index' ) {
			array_shift( $parts );
		}
		self::$controllerClass[] = $cName;
		foreach( self::$controllerClass as $k => $v ) {
			self::$controllerClass[$k] = ucFirst( $v );
		};
		self::$controllerClass = implode( self::$controllerClass ) . 'Controller';
		return $controllerFile;
	}

	/**
	 * Поиск подходящих экшенов
	 * @param string[] $parts
	 *
	 * @return bool|string
	 */
	private static function findAction( &$parts ) {

		$foundMethod = false;
		$methodNames = !empty( $parts ) ? array( $parts[0], 'index' ) : array( 'index' );
		foreach( $methodNames as $methodTmp ) {
			foreach( self::$methodTypes as $methodType ) {
				if( method_exists( self::$controllerClass, $m = $methodTmp . $methodType[0] . 'Action' . $methodType[1] ) ) {
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

	/**
	 * Возвращает имя класса найденного контроллера
	 * @return null|string
	 */
	public static function getControllerClass() {

		return self::$controllerClass;
	}

	/**
	 * Позволяет задать контроллер и экшен вручную
	 * (например, из плагина CMS)
	 *
	 * @param string $controllerClass
	 * @param string $actionMethod
	 * @param array  $parameters
	 */
	public static function setCustomAction( $controllerClass, $actionMethod, $parameters = array() ) {

		self::$controllerClass = $controllerClass;
		self::$method = $actionMethod;
		self::$parameters = $parameters;
	}

	public static function getControllerUri() {

		return self::$controllerUri;
	}
}
