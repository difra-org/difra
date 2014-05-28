<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra;

/**
 * Class Plugin
 *
 * @package Difra
 */
abstract class Plugin {

	/** @var int Версия движка, для которой обновлялся плагин */
	protected $version = 0;
	/** @var string Описание */
	protected $description = '';
	/** @var null|string|array Названия плагинов, требующихся для работы плагина */
	protected $require = null;

	private $class;
	private $name = null;
	private $enabled = false;
	private $path = '';

	/**
	 * Синглтон
	 *
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
	final public function __construct() {

		$this->class = get_class( $this );
	}

	/**
	 * Возвращает список зависимостей и т.п.
	 *
	 * @return array|null
	 */
	public function getInfo() {

		$info = array();
		// requires
		if( !property_exists( $this, 'require' ) or !$this->require ) {
			$info['requires'] = array();
		} elseif( !is_array( $this->require ) ) {
			$info['requires'] = array( $this->require );
		} else {
			$info['requires'] = $this->require;
		}
		// provides
		if( !property_exists( $this, 'provides' ) or !$this->provides ) {
			$info['provides'] = array();
		} elseif( !is_array( $this->provides ) ) {
			$info['provides'] = array( $this->provides );
		} else {
			$info['provides'] = $this->provides;
		}
		// version
		if( !property_exists( $this, 'version' ) or !$this->version ) {
			$info['version'] = 0;
		} else {
			$info['version'] = (float)$this->version;
		}
		$info['description'] = property_exists( $this, 'description' ) ? $this->description : '';
		return $info;
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
	 *
	 * @return bool
	 */
	public function enable() {

		if( $this->enabled ) {
			return false;
		}
		$this->enabled = true;
		\Difra\Unify::registerObjects( $this->getObjects() );
		return true;
	}

	/**
	 * Возвращает путь к папке плагина
	 *
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
	 *
	 * @return string
	 */
	public function getName() {

		if( !$this->name ) {
			$this->name = basename( dirname( str_replace( '\\', '/', $this->class ) ) );
		}
		return $this->name;
	}

	/**
	 * Возвращает объекты, предоставляемые плагином
	 *
	 * @return null
	 */
	public function getObjects() {

		/** @noinspection PhpUndefinedFieldInspection */
		return property_exists( $this, 'objects' ) ? $this->objects : null;
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

