<?php

namespace Difra;

class Plugger {

	/** @var string */
	private $path = '';
	/** @var string[] */
	private $pluginsNames = null;
	/** @var \Difra\Plugin[] */
	private $plugins = null;

	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	public function __construct() {

		$this->path = DIR_PLUGINS;
	}

	public function init() {

		$this->smartPluginsEnable();
	}

	/**
	 * Возвращает список всех доступных плагинов
	 * @return string[]
	 */
	private function getPluginsNames() {

		if( is_null( $this->pluginsNames ) ) {
			$plugins = array();
			if( Debugger::getInstance()->isEnabled() or !$plugins = Cache::getInstance()->get( 'plugger_plugins' ) ) {
				if( is_dir( $this->path ) and $dir = opendir( $this->path ) ) {
					while( false !== ( $subdir = readdir( $dir ) ) ) {
						if( $subdir != '.' and $subdir != '..' and is_dir( "{$this->path}/$subdir" ) ) {
							if( is_readable( "{$this->path}/$subdir/plugin.php" ) ) {
								$plugins[] = $subdir;
							}
						}
					}
				}
				Cache::getInstance()->put( 'plugger_plugins', $plugins, 300 );
			}
			$this->pluginsNames = $plugins;
		}
		return $this->pluginsNames;
	}

	/**
	 * Возвращает массив с объектами всех доступных плагинов
	 * @return \Difra\Plugin[]
	 */
	public function getAllPlugins() {

		if( is_null( $this->plugins ) ) {
			$plugins = array();
			$dirs    = $this->getPluginsNames();
			if( !empty( $dirs ) ) {
				foreach( $dirs as $dir ) {
					include( "{$this->path}/$dir/plugin.php" );
					$ucf           = ucfirst( $dir );
					$plugins[$dir] = call_user_func( array( "\\Difra\\Plugins\\$ucf\\Plugin", "getInstance" ) );
				}
			}
			$this->plugins = $plugins;
		}
		return $this->plugins;
	}

	private function smartPluginsEnable() {

		$plugins = $this->getAllPlugins();
		if( empty( $plugins ) ) {
			return;
		}
		$enabledPlugins = Config::getInstance()->get( 'plugins' );
		foreach( $plugins as $name => $obj ) {
			if( empty( $enabledPlugins ) or ( isset( $enabledPlugins[$name] ) and $enabledPlugins[$name] ) ) {
				$obj->enable();
			}
		}
	}

	public function getPaths() {

		$paths   = array();
		$plugins = $this->getAllPlugins();
		if( empty( $plugins ) ) {
			return array();
		}
		foreach( $plugins as $name=> $plugin ) {
			if( $plugin->isEnabled() ) {
				$paths[$name] = $plugin->getPath();
			}
		}
		return $paths;
	}

	/**
	 * Позволяет узнать, включен ли плагин с таким названием
	 * @param string $pluginName
	 *
	 * @return bool
	 */
	public function isEnabled( $pluginName ) {

		if( !isset( $this->plugins[$pluginName] ) ) {
			return false;
		}
		return $this->plugins[$pluginName]->isEnabled();
	}

	/**************************** Дальше идёт старый код, который в итоге будет выпилен! ****************************/

	// XXX: from pnd: пролазил везде где только можно, но не нашел ничего готового для определения наличия плагина.
	// переименовано в isEnabled()
	// Добавлено сообщение DEPRECATED 04-sep-12.
	public function isPlugin( $name ) {

		trigger_error( 'Please use Plugger->isEnabled() instead of Plugger->isPlugin()', E_USER_DEPRECATED );
		return $this->isEnabled( $name );
	}

	// Deprecated function. Added DEPRECATED MESSAGE on 04-sep-12.
	public function runDispatchers( &$controller ) {

		foreach( $this->plugins as $plugin ) {
			if( method_exists( $plugin, 'dispatch' ) ) {
				trigger_error( 'Plugin ' . $plugin . ' uses old-style dispatcher. Please use events.', E_USER_DEPRECATED );
				$plugin->dispatch( $controller );
			}
		}
	}
}
