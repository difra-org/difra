<?php

namespace Difra;

class Plugger {

	public $path;
	public $plugins = array();

	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	public function __construct() {

		$this->path = defined( 'DIR_PLUGINS' ) ? DIR_PLUGINS : realpath( dirname( __FILE__ ) . '/../../plugins' );

		if( Debugger::getInstance()->isEnabled() or !$paths = Cache::getInstance()->get( 'plugger_paths' ) ) {
			$paths = array();
			if( $dir = opendir( $this->path ) ) {
				while( false !== ( $subdir = readdir( $dir ) ) ) {
					if( $subdir != '.' and $subdir != '..' and is_dir( "{$this->path}/$subdir" ) ) {
						if( is_readable( "{$this->path}/$subdir/plugin.php" ) ) {
							$paths[] = array(
								'name' => $subdir, 'file' => "{$this->path}/$subdir/plugin.php",
								'class' => 'Difra\\Plugins\\' . ucfirst( $subdir ) . '\\Plugin'
							);
						}
					}
				}
			}
			Cache::getInstance()->put( 'plugger_paths', $paths, 300 );
		}

		foreach( $paths as $path ) {
			try {
				include_once( $path['file'] );
				$this->plugins[$path['name']] = new $path['class'];
			} catch( exception $ex ) {
				throw new exception( "Can't load plugin {$path['name']}: " . $ex->getMessage() );
			}
		}
	}

	public function runDispatchers( &$controller ) {

		foreach( $this->plugins as $name => $plugin ) {
			if( method_exists( $plugin, 'dispatch' ) ) {
				$plugin->dispatch( $controller );
			}
		}
	}

	public function getControllerDirs() {

		$dirs = array();
		if( !empty( $this->plugins ) ) {
			foreach( $this->plugins as $dir => $plugin ) {
				if( is_dir( $p = "{$this->path}/$dir/controllers/" ) ) {
					$dirs[] = $p;
				}
			}
		}
		return $dirs;
	}

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


}
