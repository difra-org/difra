<?php

namespace Difra;

class Plugger {

	// путь к папке плагинов
	private $path;
	// список всех плагинов
	private $allPlugins = array();
	// список включенных плагинов
	private $enabled = array();
	// массив загруженных плагинов
	private $plugins = array();

	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	public function __construct() {

		$this->path = defined( 'DIR_PLUGINS' ) ? DIR_PLUGINS : realpath( dirname( __FILE__ ) . '/../../plugins' );

		$this->allPlugins = $this->getAllPlugins();
		$this->enabled = $this->getEnabled( $this->allPlugins );
		sort( $this->enabled );

		foreach( $this->enabled as $plugin ) {
			$this->load( $plugin );
		}
		ksort( $this->plugins );
	}

	private function getAllPlugins() {

		static $_plugins = null;
		if( is_null( $_plugins ) ) {
			$plugins = array();
			if( Debugger::getInstance()->isEnabled() or !$plugins = Cache::getInstance()->get( 'plugger_plugins' ) ) {
				if( $dir = opendir( $this->path ) ) {
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
			$_plugins = $plugins;
		}
		return $_plugins;
	}

	private function getEnabled( $plugins ) {

		// XXX: заглушка
		return $plugins;
	}

	private function load( $plugin, $requirement = false ) {

		if( isset( $this->plugins[$plugin] ) ) {
			return;
		}
		try {
			include_once( "{$this->path}/$plugin/plugin.php" );
			$className = "\\Difra\\Plugins\\$plugin\\Plugin";
			$this->plugins[$plugin] = new $className;
			if( method_exists( $this->plugins[$plugin], 'getRequirements' ) ) {
				$req = call_user_func( array( $this->plugins[$plugin], 'getRequirements' ) );
				if( is_array( $req ) and !empty( $req ) ) {
					foreach( $req as $req1 ) {
						$this->load( $req1, true );
					}
				}
			}
			if( method_exists( $this->plugins[$plugin], 'init' ) ) {
				call_user_func( array( $this->plugins[$plugin], 'init' ) );
			}
		} catch( Exception $ex ) {
			if( !$requirement ) {
				throw new Exception( "Can't load plugin {$path['name']}: " . $ex->getMessage() );
			} else {
				throw new Exception( "Can't load dependency plugin {$path['name']}: " . $ex->getMessage() );
			}
		}
	}

	public function getDisabled() {

		static $_disabled = null;
		if( is_null( $_disabled ) ) {
			$disabled = array();
			foreach( $this->allPlugins as $name ) {
				if( !isset( $this->plugins[$name] ) ) {
					$disabled[] = array();
				}
			}
			$_disabled = $disabled;
		}
		return $_disabled;
	}
	
	public function getPath() {

		return $this->path;
	}

	public function getList() {

		return array_keys( $this->plugins );
	}

	public function getPaths() {

		static $_paths = null;
		if( is_null( $_paths ) ) {
			$paths = array();
			$list = $this->getList();
			if( !empty( $list ) ) {
				foreach( $list as $dir ) {
					$paths[] = "{$this->path}/{$dir}";
				}
			}
			$_paths = $paths;
		}
		return $_paths;
	}

	public function getPlugin( $name ) {

		return isset( $this->plugins[$name] ) ? $this->plugins[$name] : null;
	}

	public function runDispatchers( &$controller ) {

		foreach( $this->plugins as $plugin ) {
			if( method_exists( $plugin, 'dispatch' ) ) {
				$plugin->dispatch( $controller );
			}
		}
	}

	/*
	public function getFormDirs() {

		$dirs = array();
		if( !empty( $this->plugins ) ) {
			foreach( $this->plugins as $dir => $plugin ) {
				if( is_dir( $p = "{$this->path}/$dir/forms/" ) ) {
					$dirs[] = $p;
				}
			}
		}
		return $dirs;
	}
	*/
}
