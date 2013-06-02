<?php

namespace Difra;

class Plugger {

	/** @var \Difra\Plugin[] */
	private $plugins = null;
	/** @var array */
	private $pluginsData = null;
	/** @var array[] */
	private $provisions = array();

	/**
	 * Синглтон
	 * @return Plugger
	 */
	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	/**
	 * Инициализация
	 */
	public function init() {

		$this->provisions = array();
		$this->provisions['mysql'] = array( 'available' => MySQL::getInstance()->isConnected(), 'url' => '/test', 'source' => 'core' );
		$this->smartPluginsEnable();
	}

	/**
	 * Возвращает список всех доступных плагинов
	 * @return string[]
	 */
	private function getPluginsNames() {

		static $plugins = null;
		if( !is_null( $plugins ) ) {
			return $plugins;
		}
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
		return $plugins;
	}

	/**
	 * Возвращает массив с объектами всех доступных плагинов
	 * @return \Difra\Plugin[]
	 */
	public function getAllPlugins() {

		if( !is_null( $this->plugins ) ) {
			return $this->plugins;
		}
		$plugins = array();
		$dirs = $this->getPluginsNames();
		if( !empty( $dirs ) ) {
			foreach( $dirs as $dir ) {
				include( DIR_PLUGINS . '/' . $dir . '/plugin.php' );
				$ucf = ucfirst( $dir );
				$plugins[$dir] = call_user_func( array( "\\Difra\\Plugins\\$ucf\\Plugin", "getInstance" ) );
			}
		}
		return $this->plugins = $plugins;
	}

	/**
	 * Загружает включенные плагины без недостающих зависимостей
	 */
	public function smartPluginsEnable() {

		// TODO: cache me!
		if( !is_null( $this->pluginsData ) ) {
			return;
		}
		$this->pluginsData = array();
		$plugins = $this->getAllPlugins();
		if( empty( $plugins ) ) {
			return;
		}
		$enabledPlugins = Config::getInstance()->get( 'plugins' );

		// составление списка плагинов
		foreach( $plugins as $name => $plugin ) {
			$info = $plugin->getInfo();
			$this->pluginsData[$name] = array(
				'enabled' => in_array( $name, $enabledPlugins ) or ( isset( $enabledPlugins[$name] ) and $enabledPlugins[$name] ),
				'loaded' => false,
				'require' => $info['requires'],
				'provides' => $info['provides'],
				'version' => $info['version'],
				'description' => $info['description']
			);
			$provs = array_merge( array( $name ), $info['provides'] );
			foreach( $provs as $prov ) {
				if( isset( $this->provisions[$prov] ) ) {
					if( is_array( $this->provisions[$prov]['source'] ) ) {
						$this->provisions[$prov]['source'][] = $name;
					} else {
						$this->provisions[$prov]['source'] = array( $this->provisions[$prov]['source'], $name );
					}
				} else {
					$this->provisions[$prov] = array(
						'available' => false,
						'source' => $name
					);
				}
			}
		}
		// Загрузка плагинов
		do {
			$changed = false;
			foreach( $this->pluginsData as $name => $data ) {
				if( !$data['enabled'] or $data['loaded'] ) {
					// plugin is disabled or already loaded
					continue;
				}
				// check if all provisions are available
				if( !empty( $data['require'] ) ) {
					foreach( $data['require'] as $req ) {
						if( !$this->provisions[$req]['available'] ) {
							continue 2;
						}
					}
				}
				// enable plugin
				$this->plugins[$name]->enable();
				$this->pluginsData[$name]['loaded'] = true;
				$changed = true;
				// set plugin's provisions as available
				$this->provisions[$name]['available'] = true;
				foreach( $data['provides'] as $prov ) {
					$this->provisions[$prov]['available'] = true;
				}
			}
		} while( $changed );
		// Инициализация плагинов
		foreach( $this->plugins as $plugin ) {
			if( !$plugin->isEnabled() ) {
				continue;
			}
			$plugin->init();
		}
	}

	/**
	 * Заполняет информацию о недостающих плагинах и недостающей версии (missingReq, disabled, old)
	 */
	public function fillMissingReq() {

		static $didit = false;
		if( $didit ) {
			return;
		}
		$didit = true;
		foreach( $this->pluginsData as $name => $data ) {
			if( !$data['loaded'] and !empty( $data['require'] ) ) {
				foreach( $data['require'] as $req ) {
					if( !isset( $this->provisions[$req] ) or !$this->provisions[$req]['available'] ) {
						$this->pluginsData[$name]['missingReq'][] = $req;
						$this->pluginsData[$name]['disabled'] = true;
					}
				}
			}
			if( $data['version'] < (float)Site::VERSION ) {
				$this->pluginsData[$name]['old'] = true;
			}
		}
	}

	/**
	 * Возвращает информацию о плагинах в XML
	 *
	 * @param \DOMElement|\DOMNode $node
	 */
	public function getPluginsXML( $node ) {

		$this->smartPluginsEnable();
		$this->fillMissingReq();
		$pluginsNode = $node->appendChild( $node->ownerDocument->createElement( 'plugins' ) );
		\Difra\Libs\XML\DOM::array2domAttr( $pluginsNode, $this->pluginsData );
		$provisionsNode = $node->appendChild( $node->ownerDocument->createElement( 'provisions' ) );
		\Difra\Libs\XML\DOM::array2domAttr( $provisionsNode, $this->provisions );
	}

	/**
	 * Возвращает информацию о плагинах в XML
	 * @param \DOMElement|\DOMNode $node
	 */
	public function getPluginsXML( $node ) {

		$this->smartPluginsEnable();
		$this->fillMissingReq();
		$pluginsNode = $node->appendChild( $node->ownerDocument->createElement( 'plugins' ) );
		\Difra\Libs\XML\DOM::array2domAttr( $pluginsNode, $this->pluginsData );
		$provisionsNode = $node->appendChild( $node->ownerDocument->createElement( 'provisions' ) );
		\Difra\Libs\XML\DOM::array2domAttr( $provisionsNode, $this->provisions );
	}

	/**
	 * Получает пути к папкам всех включенных плагинов
	 * @return array
	 */
	public function getPaths() {

		$paths = array();
		$plugins = $this->getAllPlugins();
		if( empty( $plugins ) ) {
			return array();
		}
		foreach( $plugins as $name => $plugin ) {
			if( $plugin->isEnabled() ) {
				$paths[$name] = $plugin->getPath() . '/';
			}
		}
		return $paths;
	}

	/**
	 * Позволяет узнать, включен ли плагин с таким названием
	 * @param string $pluginName
	 * @return bool
	 */
	public function isEnabled( $pluginName ) {

		if( !isset( $this->plugins[$pluginName] ) ) {
			return false;
		}
		return $this->plugins[$pluginName]->isEnabled();
	}

	/**
	 * @deprecated
	 * @param string $name
	 * @return bool
	 */
	public function isPlugin( $name ) {

		trigger_error( 'Please use Plugger->isEnabled() instead of Plugger->isPlugin()', E_USER_DEPRECATED );
		return $this->isEnabled( $name );
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function turnOn( $name ) {

		$config = Config::getInstance();
		$conf = $config->get( 'plugins' );
		if( !isset( $this->plugins[$name] ) ) {
			return false;
		}
		if( !$conf ) {
			$conf = array();
		}
		if( !isset( $conf[$name] ) ) {
			$conf[$name] = true;
			$config->set( 'plugins', $conf );
		}
		return $config->save();
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function turnOff( $name ) {

		$config = Config::getInstance();
		$conf = $config->get( 'plugins' );
		if( isset( $conf[$name] ) ) {
			unset( $conf[$name] );
			$config->set( 'plugins', $conf );
		}
		return $config->save();
	}
}
