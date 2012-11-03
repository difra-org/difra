<?php

namespace Difra\Libs;

class Vault {

	/**
	 * Добавляет файл во временное хранилище.
	 * @param $data
	 *
	 * @return int
	 */
	static function add( $data ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( 'DELETE FROM `vault` WHERE `created`<DATE_SUB(now(),INTERVAL 3 HOUR)' );
		$db->query( "INSERT INTO `vault` SET `data`='" . $db->escape( $data ) . "'" );
		if( $id = $db->getLastId() ) {
			\Difra\Site::getInstance()->sessionStart();
			if( !isset( $_SESSION['vault'] ) ) {
				$_SESSION['vault'] = array();
			}
			$_SESSION['vault'][$id] = 1;
		}
		return $id;
	}

	/**
	 * Получает файл из временного хранилища.
	 * @param $id
	 *
	 * @return string|null
	 */
	static function get( $id ) {

		\Difra\Site::getInstance()->sessionLoad();
		if( !isset( $_SESSION['vault'] ) or !isset( $_SESSION['vault'][$id] ) ) {
			return null;
		}
		$db = \Difra\MySQL::getInstance();
		return $db->fetchOne( "SELECT `data` FROM `vault` WHERE `id`='" . $db->escape( $id ) . "'" );
	}

	/**
	 * Удаляет файл из временного хранилища.
	 * @param $id
	 */
	static function delete( $id ) {

		\Difra\Site::getInstance()->sessionLoad();
		if( !isset( $_SESSION['vault'][$id] ) ) {
			return;
		}
		unset( $_SESSION['vault'][$id] );
		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `vault` WHERE `id`='" . $db->escape( $id ) . "'" );
		$db->query( 'DELETE FROM `vault` WHERE `created`<DATE_SUB(now(),INTERVAL 3 HOUR)' );
	}

	/**
	 * Сохраняет изображения, встречающиеся в $html, в папку $path и заменяет временные пути к изображениям в img src=... на пути вида
	 * $urlPrefix/id.png.
	 * Внимание: если в $path встретятся изображения, которые не используются в $html, они будут стёрты! Поэтому для каждого
	 * отдельного элемента, содержащего HTML, нужно указывать отдельную папку! Кроме того,
	 * текст $html в процессе сохранения изображений будет изменён, поэтому сначала нужно сохранять изображения, а потом текст.
	 *
	 * @param $html
	 * @param $path
	 * @param $urlPrefix
	 */
	static function saveImages( &$html, $path, $urlPrefix ) {

		preg_match_all( '/src=\"\/up\/tmp\/([0-9]+)\"/', $html, $newImages );
		preg_match_all( '/src=\"' . preg_quote( $urlPrefix, '/' ) . '\/([0-9]+)\.png\"/', $html, $oldImages );
		if( !empty( $oldImages[1] ) ) {
			$usedImages = $oldImages[1];
		} else {
			$usedImages = array();
		}
		if( !empty( $newImages[1] ) ) {
			@mkdir( $path, 0755, true );
			$urlPrefix = trim( $urlPrefix, '/' );
			foreach( $newImages[1] as $v ) {
				$img = Vault::get( $v );
				file_put_contents( "{$path}/{$v}.png", $img );
				$html = str_replace( "src=\"/up/tmp/$v\"", "src=\"/{$urlPrefix}/{$v}.png\"", $html );
				Vault::delete( $v );
				$usedImages[] = $v;
			}
		}
		if( is_dir( $path ) ) {
			$dir = opendir( $path );
			while( false !== ( $file = readdir( $dir ) ) ) {
				if( $file{0} == '.' ) {
					continue;
				}
				if( substr( $file, -4 ) != '.png' or !in_array( substr( $file, 0, strlen( $file ) - 4 ), $usedImages ) ) {
					@unlink( "$path/$file" );
				}
			}
		}
	}
}