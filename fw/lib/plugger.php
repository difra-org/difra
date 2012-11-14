<?php

namespace Difra;

class Plugger {

	/** @var string[] */
	private $pluginsNames = null;
	/** @var \Difra\Plugin[] */
	private $plugins = null;

	/**
	 * Синглтон
	 * @return Plugger
	 */
	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
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
				if( is_dir( DIR_PLUGINS ) and $dir = opendir( DIR_PLUGINS ) ) {
					while( false !== ( $subdir = readdir( $dir ) ) ) {
						if( $subdir != '.' and $subdir != '..' and is_dir( DIR_PLUGINS . '/' . $subdir ) ) {
							if( is_readable( DIR_PLUGINS . "/$subdir/plugin.php" ) ) {
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
					include( DIR_PLUGINS . '/' . $dir . '/plugin.php' );
					$ucf           = ucfirst( $dir );
					$plugins[$dir] = call_user_func( array( "\\Difra\\Plugins\\$ucf\\Plugin", "getInstance" ) );
				}
			}
			$this->plugins = $plugins;
		}
		return $this->plugins;
	}

	/**
	 * Включает плагины
	 * @return array
	 */
	private function smartPluginsEnable() {

		static $pluginsState = null;
		if( !is_null( $pluginsState ) ) {
			return $pluginsState;
		}
		$pluginsState = array();
		$plugins      = $this->getAllPlugins();
		if( empty( $plugins ) ) {
			return $pluginsState;
		}
		$enabledPlugins = Config::getInstance()->get( 'plugins' );
		// составление списка плагинов с данными о зависимостях
		foreach( $plugins as $name => $plugin ) {
			$requirements        = $plugin->getRequirements();
			$pluginsState[$name] = array(
				'enabled'    => empty( $enabledPlugins ) or ( isset( $enabledPlugins[$name] ) and $enabledPlugins[$name] ),
				'require'    => $requirements ? $requirements : array(),
				'required'   => array(),
				'missingReq' => false,
				'disabled'   => false
			);
		}
		// для каждого плагина составляем список плагинов, которые от него зависят
		$hasMisses = false;
		foreach( $pluginsState as $name => $data ) {
			if( !empty( $data['require'] ) ) {
				foreach( $data['require'] as $req ) {
					if( isset( $pluginsState[$req] ) ) {
						$pluginsState[$req]['required'][] = $name;
					} else {
						$hasMisses                         = true;
						$pluginsState[$name]['missingReq'] = true;
					}
				}
			}
		}
		// есть не удовлетворенные зависимости — отключаем такие плагины и все, которые от них зависят
		if( $hasMisses ) {
			foreach( $pluginsState as $name => $data ) {
				if( $data['missingReq'] ) {
					$this->smartPluginDisable( $pluginsState, $name );
				}
			}
		}
		// включаем все плагины с удовлетворенными зависимостями
		foreach( $pluginsState as $name => $data ) {
			if( $data['enabled'] and !$data['disabled'] ) {
				$plugins[$name]->enable();
			}
		}
		return $pluginsState;
	}

	/**
	 * Отключает плагин в массиве для smartPluginsEnable()
	 *
	 * @param array  $state
	 * @param string $name
	 */
	private function smartPluginDisable( &$state, $name ) {

		if( $state[$name]['disabled'] ) {
			return;
		}
		$state[$name]['disabled'] = true;
		if( empty( $state[$name]['required'] ) ) {
			return;
		}
		foreach( $state[$name]['required'] as $req ) {
			$this->smartPluginDisable( $state, $req );
		}
	}

	/**
	 * Получает пути к папкам всех включенных плагинов
	 * @return array
	 */
	public function getPaths() {

		$paths   = array();
		$plugins = $this->getAllPlugins();
		if( empty( $plugins ) ) {
			return array();
		}
		foreach( $plugins as $name => $plugin ) {
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
}
