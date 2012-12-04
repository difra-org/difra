<?php

namespace Difra\Resourcer\Abstracts;

use Difra;

abstract class Common {

	protected $resources = array();
	const CACHE_TTL = 86400;

	/**
	 * Синглтон
	 * @return self
	 */
	static public function getInstance() {

		static $_instances = array();
		$name = get_called_class();
		return isset( $_instances[$name] ) ? $_instances[$name] : $_instances[$name] = new $name();
	}

	/**
	 * Проверка допустимости имени инстанса
	 * @param $instance
	 *
	 * @return bool
	 * @throws \Difra\Exception
	 */
	private function checkInstance( $instance ) {

		if( !preg_match( '/^[a-z0-9_-]+$/i', $instance ) ) {
			throw new \Difra\Exception( "Bad Resourcer instance name: '$instance'" );
		}
		return true;
	}

	/**
	 * Вывод ресурса
	 * @param $instance
	 *
	 * @return bool
	 * @throws \Difra\Exception
	 */
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

		/*
		 * отключено, пока nginx не поддерживает заголовок Vary в fastcgi_cache
		 *
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
		*/
		$enc = 'gzip';

		header( 'Content-Type: ' . $this->contentType );
		if( $enc == 'gzip' and $data = $this->compileGZ( $instance ) ) {
			//			header( 'Vary: Accept-Encoding' );
			header( 'Content-Encoding: gzip' );
		} else {
			$data = $this->compile( $instance );
		}
		if( !$modified = Difra\Cache::getInstance()->get( "{$instance}_{$this->type}_modified" ) ) {
			$modified = gmdate( 'D, d M Y H:i:s' ) . ' GMT';
		}
		\Difra\View::addExpires( \Difra\Controller::DEFAULT_CACHE );
		header( 'Last-Modified: ' . $modified );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + self::CACHE_TTL ) . ' GMT' );
		echo $data;
		return true;
	}

	/**
	 * Определяет, возможно ли вывести ресурс в браузер
	 * @return bool
	 */
	public function isPrintable() {

		return $this->printable;
	}

	/**
	 * Создаёт gz-версию ресурса.
	 *
	 * @param $instance
	 *
	 * @return string
	 */
	public function compileGZ( $instance ) {

		$cache = Difra\Cache::getInstance();
		if( $cache->adapter == 'None' or !Difra\Debugger::getInstance()->isResourceCache() ) {
			return false;
		}

		$cacheKey = "{$instance}_{$this->type}";
		if( $cached = $cache->get( $cacheKey . '_gz' ) ) {
			if( $cache->get( $cacheKey . '_gz_build' ) == \Difra\Site::getInstance()->getBuild() ) {
				return $cached;
			}
		}

		// ждём, пока удастся сделать lock, либо пока не появятся данные от другого процесса
		$busyKey   = "{$cacheKey}_gz_busy";
		$busyValue = rand( 100000, 999999 );
		while( true ) {
			if( !$currentBusy = $cache->get( $busyKey ) ) {
				// появились данные от другого процесса?
				if( $cached = $cache->get( $cacheKey . '_gz' ) and
				    $cache->get( $cacheKey . '_gz_build' ) == \Difra\Site::getInstance()->getBuild()
				) {
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
		$cache->put( $cacheKey . '_gz', $data = gzencode( $this->compile( $instance ), 9 ), self::CACHE_TTL );
		$cache->put( $cacheKey . '_gz_build', Difra\Site::getInstance()->getBuild(), self::CACHE_TTL );
		$cache->put( $cacheKey . '_gz_modified', gmdate( 'D, d M Y H:i:s' ) . ' GMT', self::CACHE_TTL );
		// снимаем lock
		$cache->remove( $busyKey );
		return $data;
	}

	/**
	 * Возвращает собранный ресурс.
	 * @param      $instance
	 * @param bool $withSources
	 *
	 * @return bool|null
	 */
	public function compile( $instance, $withSources = false ) {

		if( !$this->checkInstance( $instance ) ) {
			return false;
		}

		// get compiled from cache if available
		$cache = Difra\Cache::getInstance();

		if( $cache->adapter != 'None' and Difra\Debugger::getInstance()->isResourceCache() ) {

			$cacheKey = "{$instance}_{$this->type}";
			if( !is_null( $cached = $cache->get( $cacheKey ) ) ) {
				if( $cache->get( $cacheKey . '_build' ) == \Difra\Site::getInstance()->getBuild() ) {
					return $cached;
				}
			}

			// ждём, пока удастся сделать lock, либо пока не появятся данные от другого процесса
			$busyKey   = "{$cacheKey}_busy";
			$busyValue = rand( 100000, 999999 );
			while( true ) {
				if( !$currentBusy = $cache->get( $busyKey ) ) {
					// is data arrived?
					if( !is_null( $cached = $cache->get( $cacheKey ) ) and
					    $cache->get( $cacheKey . '_build' ) == \Difra\Site::getInstance()->getBuild()
					) {
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
			$resource = $this->realCompile( $instance, $withSources );

			// cache data
			$cache->put( $cacheKey, $resource, self::CACHE_TTL );
			$cache->put( $cacheKey . '_build', \Difra\Site::getInstance()->getBuild(), self::CACHE_TTL );
			$cache->put( $cacheKey . '_modified', gmdate( 'D, d M Y H:i:s' ) . ' GMT', self::CACHE_TTL );

			// unlock cache
			$cache->remove( $busyKey );

			return $resource;
		} else {
			return $this->realCompile( $instance, $withSources );
		}
	}

	/**
	 * Собирает ресурс.
	 * @param string $instance
	 * @param bool   $withSources
	 *
	 * @return string
	 */
	private function realCompile( $instance, $withSources = false ) {

		\Difra\Debugger::addLine( "Resource {$this->type}/{$instance} compile started" );
		$res = false;
		if( $this->find( $instance ) ) {
			$this->processDirs( $instance );
			$res = $this->processData( $instance, $withSources );
		}
		\Difra\Debugger::addLine( "Resource {$this->type}/{$instance} compile finished" );
		return $res;
	}

	/**
	 * Ищет папки ресурсов по папкам фреймворка, сайта и плагинов.
	 * @param $instance
	 *
	 * @return bool
	 * @throws \Difra\Exception
	 */
	private function find( $instance ) {

		$found   = false;
		$parents = array();
		$paths   = Difra\Plugger::getInstance()->getPaths();
		$paths   = array_merge( array(
					     DIR_SITE,
					     DIR_ROOT
					),
					$paths,
					array(
					     DIR_FW
					) );
		if( !empty( $paths ) ) {
			foreach( $paths as $dir ) {
				if( is_dir( $d = "{$dir}/{$this->type}/{$instance}" ) ) {
					$found     = true;
					$parents[] = $d;
				}
				if( is_dir( $d = "{$dir}/{$this->type}/all" ) ) {
					$parents[] = $d;
				}
			}
		}

		if( !$found ) {
			return false;
		}
		$this->addDirs( $instance, $parents );
		return true;
	}

	/**
	 * Находит названия всех возможных инстансов для данного ресурса.
	 * Внимание: это медленно и не кэшируется, НЕ должно быть использовано в пользовательской части!
	 * @return array|bool
	 */
	public function findInstances() {

		$plugger = Difra\Plugger::getInstance();

		// Формируем список папок, где будем искать ресурсы
		$parents = array(
			DIR_FW . $this->type,
			DIR_ROOT . $this->type,
			DIR_SITE . $this->type,
		);
		$paths   = $plugger->getPaths();
		if( !empty( $paths ) ) {
			foreach( $paths as $dir ) {
				$parents[] = "{$dir}/{$this->type}";
			}
		}

		if( empty( $parents ) ) {
			return false;
		}
		$instances = array();
		foreach( $parents as $path ) {
			if( !is_dir( $path ) ) {
				continue;
			}
			$dir = opendir( $path );
			while( false !== ( $subdir = readdir( $dir ) ) ) {
				if( $subdir{0} != '.' and is_dir( $path . '/' . $subdir ) ) {
					$instances[$subdir] = 1;
				}
			}
		}
		return array_keys( $instances );
	}

	/**
	 * Добавляет папки в список папок ресурсов
	 * @param string       $instance
	 * @param string|array $dirs
	 */
	private function addDirs( $instance, $dirs ) {

		if( !is_array( $dirs ) ) {
			$dirs = array( $dirs );
		}

		if( !isset( $this->resources[$instance] ) ) {
			$this->resources[$instance] = array();
		}
		if( !isset( $this->resources[$instance]['dirs'] ) ) {
			$this->resources[$instance]['dirs'] = array();
		}
		$this->resources[$instance]['dirs'] = array_merge( $this->resources[$instance]['dirs'], $dirs );
	}

	/**
	 * Ищет ресурсы по подпапкам
	 * @param $instance
	 */
	public function processDirs( $instance ) {

		if( empty( $this->resources[$instance]['dirs'] ) ) {
			return;
		}
		foreach( $this->resources[$instance]['dirs'] as $dir ) {
			$dirHandler = opendir( $dir );
			while( $dirEntry = readdir( $dirHandler ) ) {
				if( $dirEntry{0} == '.' ) {
					continue;
				}
				$entry = "$dir/$dirEntry";
				if( is_dir( $entry ) ) { // "special"
					$exp     = explode( '-', $dirEntry );
					$special = array(
						'name'    => ( sizeof( $exp ) == 2 ? $exp[0] : $dirEntry ),
						'version' => ( sizeof( $exp ) == 2 ? $exp[1] : 0 ),
						'files'   => array()
					);
					if( isset( $this->resources[$instance]['specials'][$special['name']] ) ) {
						if( $this->resources[$instance]['specials'][$special['name']]['version'] >
						    $special['version']
						) {
							continue;
						} else {
							unset( $this->resources[$instance]['specials'][$special['name']] );
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
