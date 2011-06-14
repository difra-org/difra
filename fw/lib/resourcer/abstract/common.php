<?php

abstract class Resourcer_Abstract_Common {
			
	protected $resources = array();

	static public function getInstance() {

		static $_instances = array();
		$name = get_called_class();
		return isset( $_instances[$name] ) ? $_instances[$name] : $_instances[$name] = new $name();
	}
	
	private function checkInstance( $instance ) {
		
		if( !preg_match( '/^[a-z0-9_-]+$/i', $instance ) ) {
			throw new exception( "Bad Resourcer instance name: '$instance'" );
			return false;
		}
		return true;
	}
	
	// получение ресурса по URI
	public function view( $instance ) {
		
		if( !$this->isPrintable() ) {
			throw new exception( "Resource of type '{$this->type}' is not printable" );
		}
		// откусим расширение, если оно есть
		$parts = explode( '.', $instance );
		if( sizeof( $parts ) == 2 ) {
			if( $parts[1] == $this->type ) {
				$instance = $parts[0];
			}
		}
		if( !$instance or !$this->checkInstance( $instance ) ) {
			return false;
		}
		$data = $this->compile( $instance );
		header( 'Content-Type: ' . $this->contentType );
		if( !$modified = Difra\Cache::getInstance()->smartGet( "{$instance}_{$this->type}_modified" ) ) {
			$modified = gmdate( 'D, d M Y H:i:s' ) . ' GMT';
		}
		header( 'Last-Modified: ' . $modified );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s' , time() + 3600 ) . ' GMT' );
		echo $data;
		return true;
	}

	// определяет, возможно ли вывести ресурс в браузер
	public function isPrintable() {
	
		return $this->printable;
	}
	
	// собирает всё в единый документ
	public function compile( $instance ) {
		
		if( !$this->checkInstance( $instance ) ) {
			return false;
		}
		
		// get compiled from cache if available
		$cache = Difra\Cache::getInstance();
		
		if( $cache->adapter != 'None' and !Difra\Debugger::getInstance()->isEnabled() ) {
		
			$t = microtime( true );
			$cacheKey = "{$instance}_{$this->type}";
			if( $cached = $cache->smartGet( $cacheKey ) ) {
				return $cached;
			}

			$busyKey  = "{$cacheKey}_busy";
			$busyValue = rand( 100000, 999999 );

			while( true ) {
				if( !$currentBusy = $cache->smartGet( $busyKey ) ) {
					// is data arrived?
					if( $cached = $cache->smartGet( $cacheKey ) ) {
						return $cached;
					}
					
					// try to lock cache
					$cache->smartPut( $busyKey, $busyValue, 7 );
					usleep( 5000 );
				} else {
					// is cache locked by me?
					if( $currentBusy == $busyValue ) {
						break;
					}
					
					usleep( 50000 );
				}
			}

			// compile and minify resource			
			$resource = $this->_subCompile( $instance );
			$resource = Difra\Minify::getInstance( $this->type )->minify( $resource );

			// cache data
			$cache->smartPut( $cacheKey, $resource );
			$cache->smartPut( "{$instance}_{$this->type}_modified", gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
			
			// unlock cache
			$cache->smartRemove( $busyKey );

			return $resource;
		} else {
			return $this->_subCompile( $instance );
		}
		
	}
	
	private function _subCompile( $instance ) {
	
		$this->find( $instance );
		$this->processDirs( $instance );
		return $this->processData( $instance );
	}

	// собирает папки ресурсов по папкам фреймворка, сайта и плагинов
	private function find( $instance ) {
		
		$plugger = Plugger::getInstance();
		$files = array();
		$dirs = array();
		
		// Формируем список папок, где будем искать ресурсы
		$parents = array(
				 DIR_ROOT . "fw/{$this->type}/{$instance}",
				 DIR_ROOT . "fw/{$this->type}/all",
				 DIR_SITE . "{$this->type}/{$instance}",
				 DIR_SITE . "{$this->type}/all",
				 );
		if( !empty( $plugger->plugins ) ) {
			foreach( $plugger->plugins as $dir => $plugin ) {
				$parents[] = "{$plugger->path}/{$dir}/{$this->type}/{$instance}";
				$parents[] = "{$plugger->path}/{$dir}/{$this->type}/all";
			}
		}
		
		if( empty( $parents ) ) {
			return false;
		} else {
			$this->addDirs( $instance, $parents );
			return true;
		}
	}
	
	private function addDirs( $instance, $dirs ) {
		
		// handle arrays
		if( is_array( $dirs ) ) {
			foreach( $dirs as $res ) {
				$this->addDirs( $instance, $res );
			}
			return;
		}
		
		// add item
		if( !isset( $this->resources[$instance] ) ) {
			$this->resources[$instance] = array();
		}
		if( !isset( $this->resources[$instance]['dirs'] ) ) {
			$this->resources[$instance]['dirs'] = array();
		}
		$this->resources[$instance]['dirs'][] = $dirs;
	}

	public function processDirs( $instance ) {
		
		if( empty( $this->resources[$instance]['dirs'] ) ) {
			return false;
		}
		foreach( $this->resources[$instance]['dirs'] as $dir ) {
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
					if( isset( $this->resources[$instance]['specials'][$special['name']] ) ) {
						if( $this->resources[$instance]['specials'][$special['name']]['version'] >= $special['version'] ) {
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
					$this->resources[$instance]['specials'][$special['name']] = $special;
				} elseif( is_file( $entry ) ) { // "file"
					if( !isset( $this->resources[$instance]['files'] ) ) {
						$this->resources[$instance]['files'] = array();
					}
					$this->resources[$instance]['files'][] = $entry;
				}
			}			
		}
	}
	
	public function getFiles( $instance ) {
		
		$files = array();
		if( !empty( $this->resources[$instance]['specials'] ) ) {
			foreach( $this->resources[$instance]['specials'] as $resource ) {
				if( !empty( $resource['files'] ) ) {
					$files = array_merge( $files, $resource['files'] );
				}
			}
		}
		if( !empty( $this->resources[$instance]['files'] ) ) {
			$files = array_merge( $files, $this->resources[$instance]['files'] );
		}
		return $files;
	}

}
