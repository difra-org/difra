<?php

require_once( dirname( __FILE__ ) . '/plugin.php' ); // needs to be included here because autoloader isn't loaded yet

class Plugger {

	public $path;
	public $plugins = array();

	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	public function __construct() {

		$this->path = realpath( dirname( __FILE__ ) . '/../../../../plugins' );

		if( $dir = opendir( $this->path ) ) {
			while( false !== ( $subdir = readdir( $dir ) ) ) {
				if( $subdir != '.' and $subdir != '..' and is_dir( "{$this->path}/$subdir" ) ) {
					if( is_readable( "{$this->path}/$subdir/plugin.php" ) ) {
						try {
							include_once( "{$this->path}/$subdir/plugin.php" );
							if( method_exists( $class = 'P' . ucfirst( $subdir ), 'getInstance' ) ) {
								$this->plugins[$subdir] = new $class;
							}
						} catch( exception $ex ) {
						}
					}
				}
			}
		}
		if( empty( $this->plugins ) ) {
			return false;
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

	public function getLocales( $locale ) {

		$files = array();
		if( !empty( $this->plugins ) ) {
			foreach( $this->plugins as $dir => $plugin ) {
				if( is_file( $p = "{$this->path}/$dir/locales/$locale.xml" ) ) {
					$files[] = $p;
				}
			}
		}
		return $files;
	}

	public function getTemplates( $template ) {

		$files = array();
		if( !empty( $this->plugins ) ) {
			foreach( $this->plugins as $dir => $plugin ) {
				if( is_file( $p = "{$this->path}/$dir/templates/$template" ) ) {
					$files[] = $p;
				}
			}
		}
		return $files;
	}

}
