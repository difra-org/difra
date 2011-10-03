<?php

namespace Difra\Resourcer\Abstracts;
use Difra;

abstract class Common {
			
	protected $resources = array();
	const CACHE_TTL = 86400;

	static public function getInstance() {

		static $_instances = array();
		$name = get_called_class();
		return isset( $_instances[$name] ) ? $_instances[$name] : $_instances[$name] = new $name();
	}
	
	private function checkInstance( $instance ) {
		
		if( !preg_match( '/^[a-z0-9_-]+$/i', $instance ) ) {
			throw new \Difra\Exception( "Bad Resourcer instance name: '$instance'" );
		}
		return true;
	}
	
	// получение ресурса по URI
	public function view( $instance ) {
		
		if( !$this->isPrintable() ) {
			throw new \Difra\Exception( "Resource of type '{$this->type}' is not printable" );
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


		// узнаем, поддерживает ли браузер gzip
		$enc = false;
		if( !empty( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ) {
			$encTypes = $_SERVER['HTTP_ACCEPT_ENCODING'];
			if( strpos( $encTypes, ',' ) ) {
				$encTypes = explode( ',', $encTypes );
			} else {
				$encTypes = array( $encTypes );
			}
			foreach( $encTypes as $type ) {
				$type = trim( $type );
				switch( $type ) {
				case 'gzip':
					$enc = 'gzip';
					break 2;
				}
			}
		}

		header( 'Content-Type: ' . $this->contentType );
		switch( $enc ) {
		case 'gzip':
			if( $data = $this->compileGZ( $instance ) ) {
				header( 'Vary: Accept-Encoding' );
				header( 'Content-Encoding: gzip' );
				break;
			}
		default:
			$data = $this->compile( $instance );
		}
		if( !$modified = Difra\Cache::getInstance()->get( "{$instance}_{$this->type}_modified" ) ) {
			$modified = gmdate( 'D, d M Y H:i:s' ) . ' GMT';
		}
		header( 'Last-Modified: ' . $modified );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 604800 ) . ' GMT' );
		echo $data;
		return true;
	}

	// определяет, возможно ли вывести ресурс в браузер
	public function isPrintable() {
	
		return $this->printable;
	}

	public function compileGZ( $instance ) {

		$cache = Difra\Cache::getInstance();
		if( $cache->adapter == 'None' or Difra\Debugger::getInstance()->isEnabled() ) {
			return false;
		}

		$cacheKey = "{$instance}_{$this->type}";
		if( $cached = $cache->get( $cacheKey . '_gz' ) ) {
			if( $cache->get( $cacheKey . '_gz_build' ) == \Difra\Site::getInstance()->getBuild() ) {
				return $cached;
			}
		}

		// ждём, пока удастся сделать lock, либо пока не появятся данные от другого процесса
		$busyKey = "{$cacheKey}_gz_busy";
		$busyValue = rand( 100000, 999999 );
		while( true ) {
			if( !$currentBusy = $cache->get( $busyKey ) ) {
				// появились данные от другого процесса?
				if( $cached = $cache->get( $cacheKey . '_gz' ) and
				    $cache->get( $cacheKey . '_gz_build' ) == \Difra\Site::getInstance()->getBuild() ) {
					return $cached;
				}
				// попытаемся получить блокировку
				$cache->put( $busyKey, $busyValue, 7 );
				usleep( 5000 );
			} else {
				// удалось получить блокировку?
				if( $currentBusy == $busyValue ) {
					break;
				}
				usleep( 50000 );
			}
		}
		// lock получен — кешируем данные
		$cache->put( $cacheKey . '_gz', gzencode( $this->compile( $instance ), 9 ), self::CACHE_TTL );
		$cache->put( $cacheKey . '_gz_build', Difra\Site::getInstance()->getBuild(), self::CACHE_TTL );
		$cache->put( $cacheKey . '_gz_modified', gmdate( 'D, d M Y H:i:s' ) . ' GMT', self::CACHE_TTL );
		// снимаем lock
		$cache->remove( $busyKey );
	}
	
	// собирает всё в единый документ
	public function compile( $instance ) {

		if( !$this->checkInstance( $instance ) ) {
			return false;
		}

		// get compiled from cache if available
		$cache = Difra\Cache::getInstance();

		if( $cache->adapter != 'None' and !Difra\Debugger::getInstance()->isEnabled() ) {
		
			$cacheKey = "{$instance}_{$this->type}";
			if( $cached = $cache->get( $cacheKey ) ) {
				if( $cache->get( $cacheKey . '_build' ) == \Difra\Site::getInstance()->getBuild() ) {
					return $cached;
				}
			}

			// ждём, пока удастся сделать lock, либо пока не появятся данные от другого процесса
			$busyKey  = "{$cacheKey}_busy";
			$busyValue = rand( 100000, 999999 );
			while( true ) {
				if( !$currentBusy = $cache->get( $busyKey ) and
				    $cache->get( $cacheKey . '_build' ) == \Difra\Site::getInstance()->getBuild()
				) {
					// is data arrived?
					if( $cached = $cache->get( $cacheKey ) ) {
						return $cached;
					}
					
					// try to lock cache
					$cache->put( $busyKey, $busyValue, 7 );
					usleep( 5000 );
				} else {
					// is cache locked by me?
					if( $currentBusy == $busyValue ) {
						break;
					}
					
					usleep( 50000 );
				}
			}

			// compile resource
			$resource = $this->_subCompile( $instance );

			// cache data
			$cache->put( $cacheKey, $resource, self::CACHE_TTL );
			$cache->put( $cacheKey . '_build', \Difra\Site::getInstance()->getBuild(), self::CACHE_TTL );
			$cache->put( $cacheKey . '_modified', gmdate( 'D, d M Y H:i:s' ) . ' GMT', self::CACHE_TTL );
			
			// unlock cache
			$cache->remove( $busyKey );

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
		
		$plugger = Difra\Plugger::getInstance();

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
							$name = str_replace( '.min.', '.', $specSub );
							$type = ( $name == $specSub ) ? 'raw' : 'min';
							if( !isset( $special['files'][$name] ) ) {
								$special['files'][$name] = array();
							}
							$special['files'][$name][$type] = "$entry/$specSub";
						}
					}
					$this->resources[$instance]['specials'][$special['name']] = $special;
				} elseif( is_file( $entry ) ) { // "file"
					if( !isset( $this->resources[$instance]['files'] ) ) {
						$this->resources[$instance]['files'] = array();
					}
					$name = str_replace( '.min.', '.', $entry );
					$type = ( $name == $entry ) ? 'raw' : 'min';
					if( !isset( $special['files'][$name] ) ) {
						$special['files'][$name] = array();
					}
					$this->resources[$instance]['files'][$name][$type] = $entry;
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
