<?php

namespace Difra\Plugins;

class Portfolio {

	/**
	 * Возвращает настройки размеров изображений
	 * @return array|mixed
	 */
	static public function getSizes() {

		$sizes = \Difra\Config::getInstance()->getValue( 'portfolio_settings', 'imgSizes' );
		if( !$sizes or empty( $sizes ) ) {
			$sizes = array(
				'small' => array( 70, 70 ),
				'medium' => array( 150, 150 ),
				'big' => array( 500, 500 ),
				'full' => array( 1200, 800 )
			);
		}
		$sizes['f'] = false;
		return $sizes;
	}

	/**
	 * Сохраняет картинки работы портфолио
	 * @param int $id
	 * @param array $images
	 *
	 * @throws \Difra\Exception
	 */
	public static function saveImages( $id, $images ) {

		if( !$images ) {
			return;
		} elseif( $images instanceof \Difra\Param\AjaxFile ) {
			$images = array( $images->val() );
		} elseif( $images instanceof \Difra\Param\AjaxFiles ) {
			$images = $images->val();
		} elseif( !is_array( $images ) ) {
			$images = array( $images );
		}

		$imgSizes = self::getSizes();
		if( empty( $imgSizes ) ) {
			throw new \Difra\Exception( 'Not set image sizes for portfolio' );
		}

		$savePath = DIR_DATA . 'portfolio/';
		@mkdir( $savePath, 0777, true );

		$db = \Difra\MySQL::getInstance();
		$Images = \Difra\Libs\Images::getInstance();

		$pos = intval( $db->fetchOne( "SELECT MAX(`position`) FROM `portfolio_images` WHERE `portfolio`='" . intval( $id ) . "'" ) ) + 1;

		foreach( $images as $img ) {
			$query = "INSERT INTO `portfolio_images` SET `portfolio`='" . intval( $id ) . "', `position`='" . $pos . "'";
			$db->query( $query );
			$imgId = $db->getLastId();

			foreach( $imgSizes as $k=>$size ) {

				if( $size ) {
					$tmpImg = $Images->createThumbnail( $img, $size[1], $size[2] );
				} else {
					$tmpImg = $Images->convert( $img, 'png' );
				}

				$fSize = file_put_contents( $savePath . $imgId . '-' . $k . '.png', $tmpImg );
				if( $fSize === false ) {
					throw new \Difra\Exception( 'It is impossible to save the picture in the data folder.' );
				}
			}
			++$pos;
		}
	}

	/**
	 * Изменяет позицию картинки в работе портфолио вниз на одну
	 * @param $id
	 */
	public static function imageDown( $id ) {

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `portfolio` FROM `portfolio_images` WHERE `id`='" . intval( $id ) . "'";
		$workId = $db->fetchOne( $query );

		if( empty( $workId ) ) {
			throw new \Difra\Exception( 'No object of a work portfolio' );
		}

		$items = $db->fetch( "SELECT `id`,`position` FROM `portfolio_images` WHERE `portfolio`='" . intval( $workId ) . "' ORDER BY `position`" );
		$newSort = array();
		$pos = 1;
		$next = false;
		foreach( $items as $item ) {
			if( $item['id'] != $id ) {
				$newSort[$item['id']] = $pos++;
				if( $next ) {
					$newSort[$next['id']] = $pos++;
					$next = false;
				}
			} else {
				$next = $item;
			}
		}
		if( $next ) {
			$newSort[$next['id']] = $pos;
		}

		foreach( $newSort as $k => $pos ) {
			$db->query( "UPDATE `portfolio_images` SET `position`='$pos' WHERE `id`='" . $db->escape( $k ) . "'" );
		}
	}

	/**
	 * Изменяет позицию картинки в работе портфолио вверх на один
	 * @param $id
	 *
	 * @throws \Difra\Exception
	 */
	public static function imageUp( $id ) {

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `portfolio` FROM `portfolio_images` WHERE `id`='" . intval( $id ) . "'";
		$workId = $db->fetchOne( $query );

		if( empty( $workId ) ) {
			throw new \Difra\Exception( 'No object of a work portfolio' );
		}

		$items = $db->fetch( "SELECT `id`,`position` FROM `portfolio_images` WHERE `portfolio`='" . intval( $workId ) . "' ORDER BY `position`" );
		$newSort = array();
		$pos = 1;
		$prev = false;
		foreach( $items as $item ) {
			if( $item['id'] != $id ) {
				if( $prev ) {
					$newSort[$prev['id']] = $pos++;
				}
				$prev = $item;
			} else {
				$newSort[$item['id']] = $pos++;
			}
		}
		if( $prev ) {
			$newSort[$prev['id']] = $pos;
		}
		foreach( $newSort as $k => $pos ) {
			$db->query( "UPDATE `portfolio_images` SET `position`='$pos' WHERE `id`='" . $db->escape( $k ) . "'" );
		}
	}

	/**
	 * Удаляет изображение
	 * @param $id
	 */
	public static function deleteImage( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `portfolio_images` WHERE `id`='" . intval( $id ) . "'" );

		$savePath = DIR_DATA . 'portfolio/';

		@unlink( $savePath . intval( $id ) . '-f.png' );

		$sizes = self::getSizes();
		if( $sizes ) {
			foreach( $sizes as $k=>$size ) {
				@unlink( $savePath . intval( $id ) . '-' . $k . '.png' );
			}
		}
	}

}