<?php

namespace Difra;

/**
 * Class Events
 *
 * @package Difra
 */
class Events {

	/** @var array */
	private static $types = array(
		'core-init', // загрузка нужных классов
		'plugins-load', // загрузка плагинов
		'config', // загрузка полных настроек
		'plugins-init', // инициализация плагинов

		'pre-action', // событие, позволяющее переопределить стандартный поиск действия
		'action-find', // стандартный поиск действия
		'init-done', // выход из состояния инициализации сайта

		'action-run', // выполнение найденного действия

		'dispatch', // выполнение диспатчеров

		'render-init', // подготовка к рендеру
		'render-run', // рендер

		'done' // статистика и прочее
	);

	/** @var array */
	private static $events = null;

	/**
	 * Базовый набор событий для работы движка
	 */
	public static function init() {

		static $initDone = false;
		if( $initDone ) {
			return;
		}
		$initDone = true;

		foreach( self::$types as $type ) {
			self::$events[$type] = array();
		}

		self::register( 'core-init', 'Difra\\Debugger', 'init' );
		self::register( 'core-init', 'Difra\\Envi\\Setup', 'run' );
		self::register( 'core-init', 'Difra\\Envi\\Session', 'init' );
		self::register( 'plugins-load', 'Difra\\Plugger', 'init' );
		if( Envi::getMode() == 'web' ) {
			self::register( 'action-find', 'Difra\\Controller', 'init' );
			self::register( 'action-run', 'Difra\\Controller', 'run' );
			self::register( 'render-run', 'Difra\\Controller', 'render' );
		}
		if( file_exists( $initPHP = ( DIR_ROOT . '/lib/init.php' ) ) ) {
			/** @noinspection PhpIncludeInspection */
			include_once( $initPHP );
		}
	}

	/**
	 * Зарегистрировать обработчик события (статический вариант)
	 *
	 * @param             $type          Имя события
	 * @param             $class         Класс обработчика (должен содержать синглтон getInstance)
	 * @param bool|string $method        Метод обработчика (если false, будет вызван только getInstance)
	 *
	 * @throws Exception
	 */
	public static function register( $type, $class, $method = false ) {

		self::init();
		if( !in_array( $type, self::$types ) ) {
			throw new Exception( 'Invalid event type: ' . $type );
		}
		self::$events[$type][] = array(
			'class' => $class,
			'method' => $method
		);
	}

	/**
	 * Вызывает все события в нужном порядке
	 */
	public static function run() {

		self::init();
		foreach( self::$events as $type => $foo ) {
			Debugger::addEventLine( 'Event ' . $type . ' started' );
			self::start( $type );
		}

		Debugger::addLine( 'Done running events' );
		if( Envi::getMode() == 'web' ) {
			Debugger::checkSlow();
		}
	}

	/**
	 * Вызывает обрабочики указанного события
	 *
	 * @param $event
	 */
	private static function start( $event ) {

		$handlers = self::$events[$event];
		if( empty( $handlers ) ) {
			return;
		}
		foreach( $handlers as $handler ) {
			Debugger::addEventLine( 'Handler for ' . $event . ': ' . $handler['class'] . '->' . ( $handler['method']
							? $handler['method'] : 'getInstance' ) . ' started' );
			call_user_func( array( $handler['class'], $handler['method'] ) );
		}
	}
}