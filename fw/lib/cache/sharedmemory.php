<?php

namespace Difra\Cache;

/**
 * Реализация кэширования в SharedMemory
 * TODO: Этот функионал давно не использовался и требует проверки.
 * Class SharedMemory
 *
 * @package Difra\Cache
 */
class SharedMemory extends Common {

	public $adapter = \Difra\Cache::INST_SHAREDMEM;

	const SHM_BLOCK_ID = 0x2648;
	const SHM_BLOCK_INDEX_SIZE = 40960;
	const SHM_BLOCK_SEGMENT_SIZE = 655360;
	const SHM_BLOCK_PERMS = 0666;

	/**
	 * Проверка доступности
	 * @return bool
	 */
	public static function isAvailable() {

		// TODO: это не достаточная проверка, иногда бывают ошибки
		return extension_loaded( 'shmop' ) and php_sapi_name() != 'cli';
	}

	/**
	 * Получение данных из кэша
	 * @param string  $id
	 * @param boolean $doNotTestCacheValidity
	 *
	 * @return string
	 */
	public function realGet( $id, $doNotTestCacheValidity = false ) {

		$shm_id = shm_attach( self::SHM_BLOCK_ID );
		if( $shm_id ) {
			$size = @shm_get_var( $shm_id, '1' );
			$size = hexdec( $size );
			// Fetch the structure
			if( $size > 0 ) {
				$structure = shm_get_var( $shm_id, '0' );
				shm_detach( $shm_id );
				if( isset( $structure[$id] ) ) {
					$seg_id = shm_attach( self::SHM_BLOCK_ID + $structure[$id] );
					if( $seg_id ) {
						// Get the size of the structure
						$cache_size = shm_get_var( $seg_id, '1' );
						$cache_size = hexdec( $cache_size );
						$cache_data = shm_get_var( $seg_id, '0' );
						shm_detach( $seg_id );
						return $cache_data;
					} else {
						return false;
					}
				}
			} else {
				shm_detach( $shm_id );
			}
		}
		return null;
	}

	/**
	 * Проверка существования ключа
	 * @param string $id
	 *
	 * @return boolean
	 */
	public function test( $id ) {

		$data = $this->get( $id );
		return !empty( $data );
	}

	/**
	 * Сохранение данных в кэше
	 * @param string   $id
	 * @param string   $data
	 * @param bool|int $specificLifetime
	 *
	 * @return bool
	 */
	public function realPut( $id, $data, $specificLifetime = false ) {

		//return shm_put_var( $this->_shm_id, $id, $data );
		// First read cache structure from shared memory
		$struc_id = shm_attach( self::SHM_BLOCK_ID, self::SHM_BLOCK_INDEX_SIZE, self::SHM_BLOCK_PERMS );

		if( $struc_id ) {
			// Get the size of the structure
			if( shm_has_var( $struc_id, '1' ) ) {
				$size = shm_get_var( $struc_id, '1' );
				$size = hexdec( $size );
			} else {
				$size = 0;
			}

			// Fetch the structure
			if( $size > 0 ) {
				$structure = shm_get_var( $struc_id, '0' );
			} else {
				$structure = array();
			}
			// Get highest segment id
			$highest = 0;
			reset( $structure );
			foreach( $structure as $v ) {
				if( $v > $highest ) {
					$highest = $v;
				}
			}

			// Get lowest unused segment id
			$segment = 0;
			for( $i = 1; $i <= $highest + 1; $i++ ) {
				if( !in_array( $i, $structure ) ) {
					$segment = $i;
					break;
				}
			}

			$delete = isset( $structure[$id] ) ? $structure[$id] : false;
			$seg_id = shm_attach( self::SHM_BLOCK_ID + $segment, self::SHM_BLOCK_SEGMENT_SIZE, self::SHM_BLOCK_PERMS );

			if( $seg_id ) {
				// Store data
				$seg_data = serialize( $data );
				$seg_size = @strlen( $seg_data );
				$seg_size = sprintf( '%04X', $seg_size );

				shm_put_var( $seg_id, '1', $seg_size );
				shm_put_var( $seg_id, '0', $seg_data );
				shm_detach( $seg_id );

				// Update structure
				$structure[$id] = $segment;

				// Store the structure
				$struc_data = $structure;

				$struc_size = @strlen( $struc_data );
				$struc_size = sprintf( '%04X', $struc_size );

				shm_put_var( $struc_id, '1', $struc_size );
				shm_put_var( $struc_id, '0', $struc_data );
				shm_detach( $struc_id );

				// Delete old segment
				if( $delete ) {
					$del_id = shm_attach( self::SHM_BLOCK_ID + $delete );
					shm_remove( $del_id );
					shm_detach( $del_id );
				}
				return true;
			} else {
				shm_detach( $struc_id );
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Удаление данных
	 * @param string $id
	 *
	 * @return boolean
	 */
	public function realRemove( $id ) {

		//return @shm_remove_var( $this->_shm_id, $id );
		$struc_id = shm_attach( self::SHM_BLOCK_ID, self::SHM_BLOCK_INDEX_SIZE, self::SHM_BLOCK_PERMS );

		if( $struc_id ) {
			// Get the size of the structure    
			$size = @shm_get_var( $struc_id, '1' );
			$size = hexdec( $size );

			// Fetch the structure    
			if( $size > 0 ) {
				$structure = unserialize( shm_get_var( $struc_id, '0' ) );
			} else {
				return false;
			}
			if( $id != '' && isset( $structure[$id] ) ) {
				$delete = $structure[$id];

				// Update structure
				unset( $structure[$id] );
				// Store the structure
				$struc_data = $structure;
				$struc_size = @strlen( $struc_data );
				$struc_size = sprintf( '%04X', $struc_size );
				shm_put_var( $struc_id, '1', $struc_size );
				shm_put_var( $struc_id, '0', $struc_data );
				shm_detach( $struc_id );

				// Delete old segment
				$del_id = shm_attach( self::SHM_BLOCK_ID + $delete );
				shm_remove( $del_id );
				shm_detach( $del_id );

				return true;
			}

			if( $id == '' ) {
				foreach( $structure as $v ) {
					// Delete old segment
					$del_id = shm_attach( self::SHM_BLOCK_ID + $v );
					shm_remove( $del_id );
					shm_detach( $del_id );
				}

				$structure = array();

				// Store the structure
				$struc_data = $structure;
				$struc_size = strlen( $struc_data );
				$struc_size = sprintf( '%04X', $struc_size );
				shm_put_var( $struc_id, '1', $struc_size );
				shm_put_var( $struc_id, '0', $struc_data );
				shm_detach( $struc_id );
				return true;
			}
		}

		return false;
	}

	/**
	 * Очистка кэша
	 * @param string $mode
	 *
	 * @return boolean true if no problem
	 */
	public function clean( $mode = '' ) {

		//return shm_remove( $this->_shm_id );
		$this->remove( '' );
	}

	/**
	 * Получение информации о Shared Memory
	 *
	 * @return array|bool
	 */
	public function cacheInfo() {

		// First read cache structure from shared memory
		$struc_id = shm_attach( self::SHM_BLOCK_ID );
		if( $struc_id ) {
			// Get the size of the structure
			$structure = @shm_get_var( $struc_id, '0' );
			shm_detach( $struc_id );
			$result = array();
			while( list( $k, $v ) = each( $structure ) ) {
				// attach to the current segment
				$info_id = shm_attach( self::SHM_BLOCK_ID + $v );

				// Get the size of the structure
				$result[$k] = sizeof( shm_get_var( $info_id, '0' ) );

				// detach from this element
				shm_detach( $info_id );
			}

			return $result;
		}

		return false;
	}

	/**
	 * Определяет наличие автоматической подчистки кэша
	 * @return boolean
	 */
	public function isAutomaticCleaningAvailable() {

		return false;
	}
}
