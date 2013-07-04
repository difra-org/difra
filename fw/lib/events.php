<?php

namespace Difra;

/**
 * Class Events
 *
 * @package Difra
 */
class Events {

	private $types = array(
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
	private $events = null;

	/**
	 * Синглтон
	 * @return Events
	 */
	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Конструктор
	 */
	public function __construct() {

		foreach( $this->types as $type ) {
			$this->events[$type] = array();
		}
	}

	/**
	 * Деструктор
	 */
	public function __destruct() {
	}

	/**
	 * Базовый набор событий для работы движка
	 */
	public static function init() {

		static $initDone = false;
		if( $initDone ) {
			return;
		}
		$initDone = true;

		self::register( 'core-init', 'Difra\\Site', 'init' );
		self::register( 'core-init', 'Difra\\Debugger' );
		self::register( 'plugins-load', 'Difra\Plugger', 'init' );
		self::register( 'init-done', 'Difra\\Site', 'initDone' );
		if( Envi::getMode() == 'web' ) {
			self::register( 'action-find', 'Difra\\Action', 'find' );
			self::register( 'action-run', 'Difra\\Action', 'run' );
			self::register( 'render-run', 'Difra\\Action', 'render' );
			if( file_exists( $initPHP = ( __DIR__ . '/../../lib/init.php' ) ) ) {
				/** @noinspection PhpIncludeInspection */
				include_once( $initPHP );
			}
		}
	}

	/**
	 * Зарегистрировать обработчик события (статический вариант)
	 * @param             $type          Имя события
	 * @param             $class         Класс обработчика (должен содержать синглтон getInstance)
	 * @param bool|string $method        Метод обработчика (если false, будет вызван только getInstance)
	 */
	public static function register( $type, $class, $method = false ) {

		self::getInstance()->add( $type, $class, $method );
	}

	/**
	 * Зарегистрировать обработчик события (динамический вариант)
	 * @param             $type          Имя события
	 * @param             $class         Класс обработчика (должен содержать синглтон getInstance)
	 * @param bool|string $method        Метод обработчика (если false, будет вызван только getInstance)
	 * @throws Exception
	 */
	private function add( $type, $class, $method = false ) {

		if( !in_array( $type, $this->types ) ) {
			throw new Exception( 'Invalid event type: ' . $type );
		}
		$this->events[$type][] = array(
			'class' => $class,
			'method' => $method
		);
	}

	/**
	 * Вызывает все события в нужном порядке
	 */
	public function run() {

		self::init();
		foreach( $this->events as $type => $foo ) {
			Debugger::addEventLine( 'Event ' . $type . ' started' );
			$this->start( $type );
		}

		Debugger::addLine( 'Done running events' );
		if( Envi::getMode() == 'web' ) {
			Debugger::checkSlow();
		}
	}

	/**
	 * Вызывает обрабочики указанного события
	 * @param $event
	 */
	public function start( $event ) {

		$handlers = $this->events[$event];
		if( empty( $handlers ) ) {
			return;
		}
		foreach( $handlers as $handler ) {
			Debugger::addEventLine( 'Handler for ' . $event . ': ' . $handler['class'] . '->' . ( $handler['method']
				? $handler['method'] : 'getInstance' ) . ' started' );
			$inst = call_user_func( array( $handler['class'], 'getInstance' ) );
			if( $handler['method'] ) {
				$inst->{$handler['method']}();
			}
		}
	}
}