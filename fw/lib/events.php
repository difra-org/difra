<?php

namespace Difra;

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

	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct() {

		foreach( $this->types as $type ) {
			$this->events[$type] = array();
		}
	}

	public function __destruct() {
	}

	public static function register( $type, $class, $method = false ) {

		self::getInstance()->add( $type, $class, $method );
	}

	private function add( $type, $class, $method = false ) {

		if( !in_array( $type, $this->types ) ) {
			throw new \Difra\Exception( 'Invalid event type: ' . $type );
		}
		$this->events[$type][] = array(
			'class'  => $class,
			'method' => $method
		);
	}

	public function run() {

		Site::getInstance(); // инициализация сайта
		foreach( $this->events as $type => $foo ) {
			$handlers = $this->events[$type];
			Debugger::addEventLine( 'Event ' . $type . ' started' );
			if( empty( $handlers ) ) {
				continue;
			}
			foreach( $handlers as $handler ) {
				Debugger::addEventLine( 'Event ' . $type . ' > ' . $handler['class'] . '->' . ( $handler['method']
					? $handler['method'] : 'getInstance' ) );
				$inst = call_user_func( array( $handler['class'], 'getInstance' ) );
				if( $handler['method'] ) {
					$inst->{$handler['method']}();
				}
			}
		}
	}
}