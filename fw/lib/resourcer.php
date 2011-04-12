<?php

class Resourcer {
	
	private $instance;

	private $resources = array();
	private $supportedTypes = array( 'js', 'css', 'templates' );
	
	public static function getInstance( $instance = 'main' ) {
		
		static $_instances = array();
		if( !ctype_alnum( $instance ) ) {
			return null;
		}
		return isset( $_instances[$instance] ) ? $_instances[$instance] : $_instances[$instance] = new self( $instance );
	}
	
	public static function isViewable( $type ) {
		
		$supported = array( 'js', 'css' );
		return in_array( $type, $supported );
	}
	
	public static function getResource( $path, $echo = false ) {
		
		if( empty( $path ) or !self::isViewable( $path[0] ) ) {
			return false;
		}
		$type = array_shift( $path );
		$instance = !empty( $path ) ? array_shift( $path ) : 'main';
		// откусим расширение
		if( strlen( $instance ) > strlen( $type ) ) {
			if( substr( $instance, - strlen( $type ) - 1 ) == ".$type" ) {
				$instance = substr( $instance, 0, strlen( $instance ) - strlen( $type ) - 1 );
			}
		}
		$realInstance = self::getInstance( $instance );
		return $realInstance ? $realInstance->get( $type, $echo ) : false;
	}
	
	public function __construct( $instance ) {
	
		$this->instance = $instance;
	}
	
	private function findDirs( $type ) {
		
		if( !in_array( $type, $this->supportedTypes ) ) {
			throw new exception( 'Unknown resource type: ' . $type );
			return false;
		}
		$plugger = Plugger::getInstance();
		$files = array();
		$dirs = array();
		
		// Формируем список папок, где будем искать ресурсы
		$parents = array(
				 DIR_ROOT . "$type/{$this->instance}",
				 DIR_ROOT . "$type/all",
				 DIR_SITE . "$type/{$this->instance}",
				 DIR_SITE . "$type/all",
				 );
		if( !empty( $plugger->plugins ) ) {
			foreach( $plugger->plugins as $dir => $plugin ) {
				$parents[] = "{$plugger->path}/$dir/$type/{$this->instance}";
				$parents[] = "{$plugger->path}/$dir/$type/all";
			}
		}
		
		if( empty( $parents ) ) {
			return false;
		} else {
			$this->addDirs( $type, $parents );
			return true;
		}
	}
	
	/**
	 * Добавляет список папок ресурсов
	 * $type	тип (например, js)
	 * $data	ресурс или массив ресурсов
	 */
	public function addDirs( $type, $data ) {
		
		// handle arrays
		if( is_array( $data ) ) {
			foreach( $data as $res ) {
				$this->addDirs( $type, $res );
			}
			return true;
		}
		
		// add item
		if( !isset( $this->resources[$type] ) ) {
			$this->resources[$type] = array();
		}
		if( !isset( $this->resources[$type]['dirs'] ) ) {
			$this->resources[$type]['dirs'] = array();
		}
		$this->resources[$type]['dirs'][] = $data;
		return true;
	}
	
	public function processDirs( $type ) {
		
		if( empty( $this->resources[$type]['dirs'] ) ) {
			return false;
		}
		foreach( $this->resources[$type]['dirs'] as $dir ) {
			if( !is_dir( $dir ) ) {
				continue;
			}
			$dirHandler = opendir( $dir );
			while( $dirEntry = readdir( $dirHandler ) ) {
				if( $dirEntry{0} == '.' ) {
					continue;
				}
				$entry = "$dir/$dirEntry";
				if( is_dir( $entry ) ) { // "special"
					$exp = explode( '-', $dirEntry );
					$special = array(
						'name' => ( sizeof( $exp ) == 2 ? $exp[0] : $dirEntry ),
						'version' => ( sizeof( $exp ) == 2 ? $exp[1] : 0 ),
						'files' => array()
					);
					if( isset( $this->resources[$type]['specials'][$special['name']] ) ) {
						if( $this->resources[$type]['specials'][$special['name']]['version'] >= $special['version'] ) {
							continue;
						}
					}
					$specHandler = opendir( $entry );
					while( $specSub = readdir( $specHandler ) ) {
						if( $specSub{0} == '.' ) {
							continue;
						}
						if( is_file( "$entry/$specSub" ) ) {
							 $special['files'][] = "$entry/$specSub";
						}
					}
					$this->resources[$type]['specials'][$special['name']] = $special;
				} elseif( is_file( $entry ) ) { // "file"
					if( !isset( $this->resources[$type]['files'] ) ) {
						$this->resources[$type]['files'] = array();
					}
					$this->resources[$type]['files'][] = $entry;
				}
			}			
		}
	}
	
	public function get( $type, $echo = false ) {
	
		if( !$this->isViewable( $type ) ) {
			return false;
		}
		$data = $this->_compile( $type );
		if( $echo ) {
			switch( $type ) {
				case 'css':
					header( 'Content-Type: text/css' );
					break;
				case 'js':
					header( 'Content-Type: application/x-javascript' );
					break;
			}
			header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' , time() + 3600 ) . ' GMT' );
			echo $data;
			return true;
		} else {
			return $data;
		}
	}
	
	private function _compile( $type ) {
		
		// get compiled from cache if available
		$cacheKey = Site::getInstance()->project . "1_{$this->instance}_$type";
		if( $cached = Cache::getInstance()->get( $cacheKey ) ) {
			if( $cached['version'] == Site::getInstance()->bigVersion ) {
				return $cached['data'];
			}
		}
		
		// compile new data
		$this->findDirs( $type, $this->instance );
		$this->processDirs( $type );
		$data = array();
		if( !empty( $this->resources[$type]['specials'] ) ) {
			foreach( $this->resources[$type]['specials'] as $resource ) {
				if( !empty( $resource['files'] ) ) {
					foreach( $resource['files'] as $file ) {
						$data[] = file_get_contents( $file );
					}
				}
			}
		}
		if( !empty( $this->resources[$type]['files'] ) ) {
			foreach( $this->resources[$type]['files'] as $file ) {
				$data[] = file_get_contents( $file );
			}
		}
		$data = implode( "\n", $data );
		
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
}
