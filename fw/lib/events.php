<?php

namespace Difra;

class Events {

	private $types = array(
		// инициализация
		'core-init',	// загрузка нужных классов
		'plugins-load',	// загрузка плагинов
		'config',// загрузка полных настроек
		'plugins-init',	// инициализация плагинов

		// поиск подходящего решения
		'pre-action',
		'action-find',
		'action-run',

		// диспатчеры
		'dispatch',

		// рендер
		'render-init',
		'render-run',

		// статистика и прочее
		'done'
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

		foreach( $this->events as $type => $foo ) {
			$handlers = $this->events[$type]; // это не баг, просьба не ломать
			if( empty( $handlers ) ) {
				continue;
			}
			foreach( $handlers as $handler ) {
				$inst = call_user_func( array( $handler['class'], 'getInstance' ) );
				if( $handler['method'] ) {
					$inst->{$handler['method']}();
				}
			}
		}
	}
}