<?php

class Resourcer {
	
	private $instance;

	private $css = array();
	private $cssFiles = array();
	private $js  = array();
	private $jsFiles = array();
	private $jsSpecialFiles = array();
	
	public static function getInstance( $instance = 'main' ) {
		
		static $_instances = array();
		if( !ctype_alnum( $instance ) ) {
			return null;
		}
		return isset( $_instances[$instance] ) ? $_instances[$instance] : $_instances[$instance] = new self( $instance );
	}
	
	public static function isSupported( $type ) {
		
		$supported = array(
				   'css',
				   'js'
				   );
		return in_array( $type, $supported );
	}
	
	public static function getResource( $path, $echo = false ) {
		
		if( empty( $path ) or !self::isSupported( $path[0] ) ) {
			return false;
		}
		$type = array_shift( $path );
		$instance = !empty( $path ) ? array_shift( $path ) : 'main';
		$realInstance = self::getInstance( $instance );
		return $realInstance ? $realInstance->get( $type, $echo ) : false;
	}
	
	public function __construct( $instance ) {
	
		$this->instance = $instance;
	}
	
	private function _collect( $type ) {
		
		if( !self::isSupported( $type ) ) {
			return false;
		}
		$files = array();
		$plugger = Plugger::getInstance();
		$dirs = array();
		
		// Формируем список папок, где будем искать ресурсы
		$parents = array();
		$parents[] = DIR_ROOT . "$type/{$this->instance}";
		$parents[] = DIR_ROOT . "$type/all";
		$parents[] = DIR_SITE . "$type/{$this->instance}";
		$parents[] = DIR_SITE . "$type/all";
		if( !empty( $plugger->plugins ) ) {
			foreach( $plugger->plugins as $dir => $plugin ) {
				$parents[] = "{$plugger->path}/$dir/$type/{$this->instance}";
				$parents[] = "{$plugger->path}/$dir/$type/all";
			}
		}
		
		// Формируем список ресурсов
		foreach( $parents as $dir ) {
			if( is_dir( $dir ) ) {
				$dirHandler = opendir( $dir );
				while( $dirEntry = readdir( $dirHandler ) ) {
					if( $dirEntry{0} != '.' ) {
						$dirs[] = "$dir/$dirEntry";
					}
				}
			}
		}
		if( empty( $dirs ) ) {
			return false;
		}
		foreach( $dirs as $dirEntry ) {
			// Добавляем обычные ресурсы
			if( is_file( $dirEntry ) ) {
				$this->{'add'.$type.'File'}( $dirEntry );
			}
			// Добавляем именованные ресурсы (из подпапок)
			if( is_dir( "$dirEntry" ) ) {
				$dir2Handler = opendir( "$dirEntry" );
				while( $dir2Entry = readdir( $dir2Handler ) ) {
					$specials = array();
					if( $dir2Entry{0} != '.' and is_file( "$dirEntry/$dir2Entry" ) ) {
						$specials[] = "$dirEntry/$dir2Entry";
					}
					$this->{'add'.$type.'Special'}( $dirEntry, $specials );
				}
			}
		}
		return true;
	}

	public function addCSS( $data ) {
		
		$this->css[] = $data;
	}

	public function addCSSSpecial( $name, $data, $version = 0 ) {
		
		if( empty( $data ) ) {
			return false;
		}
		if( !isset( $this->cssSpecialFiles[$name] ) or $this->cssSpecialFiles[$name]['version'] < $version ) {
			$this->cssSpecialFiles[$name] = array(
							     'version' => $version,
							     'data'    => $data
							     );
		}
	}
	
	public function addCSSFile( $file ) {
		
		$this->cssFiles[] = $file;
	}
	
	public function addJS( $data ) {
		
		$this->js[] = $data;
	}
	
	/**
	 * Разные библиотеки (к примеру, jQuery) следует добавлять через эту функцию.
	 * Можно несколько раз добавить одну и ту же библиотеку из разных плагинов и в
	 * результате будет выдана только самая новая версия.
	 */
	public function addJSSpecial( $name, $data, $version = 0 ) {
	
		if( empty( $data ) ) {
			return false;
		}
		if( !isset( $this->jsSpecialFiles[$name] ) or $this->jsSpecialFiles[$name]['version'] < $version ) {
			$this->jsSpecialFiles[$name] = array(
							     'version' => $version,
							     'data'    => $data
							     );
		}
	}
	
	public function addJSFile( $file ) {
		
		$this->jsFiles[] = $file;
	}
	
	public function get( $type, $echo = false ) {
	
		switch( $type ) {
			case 'js':
				return $this->getJS( $echo );
			case 'css':
				return $this->getCSS( $echo );
			default:
				return false;
		}
	}
	
	private function _compile( $type ) {
		
		// get compiled from cache if available
		$cacheKey = Site::getInstance()->project . "_{$this->instance}_$type";
		if( $cached = Cache::getInstance()->get( $cacheKey ) ) {
			if( $cached['version'] == Site::getInstance()->bigVersion ) {
				return $cached['data'];
			}
		}
		
		// compile new data
		$this->_collect( $type, $this->instance );
		$data = '';
		if( !empty( $this->{$type} ) ) {
			$data = implode( "\n", $this->{$type} );
		}
		if( !empty( $this->{$type.'Files'} ) ) {
			foreach( $this->{$type.'Files'} as $file ) {
				$data .= file_get_contents( $file ) . "\n";
			}
		}
		if( !empty( $this->{$type.'SpecialFiles'} ) ) {
			foreach( $this->{$type.'SpecialFiles'} as $file ) {
				foreach( $file['data'] as $f ) {
					$data .= file_get_contents( $f ) . "\n";
				}
			}
		}
		
		// save compiled data to cache
		Cache::getInstance()->put(
					  $cacheKey,
					  array(
						'version' => Site::getInstance()->bigVersion,
						'data'    => $data
						),
					  0,
					  60
					  );

		return $data;
	}
	
	public function getCSS( $echo ) {
	
		$data = $this->_compile( 'css' );
		if( $echo ) {
			header( 'Content-type: text/css' );
			echo $data;
			return true;
		} else {
			return $data;
		}
	}
	
	public function getJS( $echo ) {
	
		$data = $this->_compile( 'js' );
		if( $echo ) {
			header( 'Content-type: application/x-javascript' );
			echo $data;
			return true;
		} else {
			return $data;
		}
	}
}