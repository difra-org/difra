<?php

namespace Difra;

abstract class Plugin {

	private $class;
	private $name = null;
	private $enabled = false;
	private $path = '';

	/**
	 * Синглтон
	 * @return self
	 */
	static public function getInstance() {

		static $_self = array();
		$class = get_called_class();
		return !empty( $_self[$class] ) ? $_self[$class] : $_self[$class] = new $class;
	}

	/**
	 * Конструктор
	 */
	final function __construct() {

		$this->class = get_class( $this );
	}

	/**
	 * Возвращает список зависимостей
	 * @return array|null
	 */
	public function getRequirements() {

		return property_exists( $this, 'require' ) ? $this->require : null;
	}

	abstract public function init();

	/**
	 * Возвращает true, если плагин включен
	 *
	 * @return bool
	 */
	public function isEnabled() {

		return $this->enabled;
	}

	/**
	 * Включает плагин
	 * @return bool
	 */
	public function enable() {

		if( $this->enabled ) {
			return false;
		}
		$this->enabled = true;
		if( $requirements = $this->getRequirements() ) {
			$plugger = Plugger::getInstance();
			$plugins = $plugger->getAllPlugins();
			foreach( $requirements as $req ) {
				if( !isset( $plugins[$req] ) ) {
					return false;
				}
				$plugins[$req]->enable();
			}
		}
		$this->init();
		return true;
	}

	/**
	 * Возвращает путь к папке плагина
	 * @return string
	 */
	public function getPath() {

		if( !$this->path ) {
			$reflection = new \ReflectionClass( $this );
			$this->path = dirname( $reflection->getFileName() );
		}
		return $this->path;
	}

	/**
	 * Возвращает название плагина
	 * @return string
	 */
	public function getName() {

		if( !$this->name ) {
			$this->name = basename( dirname( str_replace( '\\', '/', $this->class ) ) );
		}
		return $this->name;
	}

	/**
	 * Возвращает Sitemap в виде массива следующих элементов:
	 * array(
	 *         'loc' => 'http://example.com/page',
	 *         'lastmod' => '2005-01-01',
	 *         'changefreq' => 'monthly',
	 *         'priority' => 0.8
	 * )
	 * Обязательным является только поле loc
	 *
	 * @return array|bool
	 */
	public function getSitemap() {

		return false;
	}
}

