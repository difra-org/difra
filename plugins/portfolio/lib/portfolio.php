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
					$tmpImg = $Images->createThumbnail( $img, $size[0], $size[1] );
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

	/**
	 * Возвращает в xml главные картинки работ по их id
	 * @param array $ids
	 * @param \DOMNode $node
	 */
	public static function getMainImagesXML( array $ids, \DOMNode $node ) {

		$db = \Difra\MySQL::getInstance();
		$ids = array_map( 'intval', $ids );
		$query = "SELECT `id`, `portfolio` FROM `portfolio_images` WHERE `position`=1 AND `portfolio` IN (" . implode( ', ', $ids ) . ")";
		$res = $db->fetch( $query );

		if( !empty( $res ) ) {
			foreach( $res as $k=>$data ) {
				$imageNode = $node->appendChild( $node->ownerDocument->createElement( 'image' ) );
				foreach( $data as $key=>$value ) {
					$imageNode->setAttribute( $key, $value );
				}
			}
		}
	}

	/**
	 * Возвращает в xml все картинки работы по её id
	 * @param          $workId
	 * @param \DOMNode $node
	 */
	public static function getWorkImagesXML( $workId, \DOMNode $node ) {

		$images = new \Difra\Unify\Search( 'PortfolioImages' );
		$images->addCondition( 'portfolio', $workId );
		$images->setOrder( 'position' );
		$images->getListXML( $node );
	}

	/**
	 * Проверяет на дубликаты генерируемый ури работы портфолио
	 * @param $title
	 *
	 * @return bool
	 */
	public static function checkURI( $title ) {

		$entry = new \Difra\Unify\Search( 'PortfolioEntry' );
		$entry->addCondition( 'uri', \Difra\Locales::getInstance()->makeLink( $title ) );
		$list = $entry->getList();
		return !is_null( $list ) ? false : true;
	}

	/**
	 * Возвращает массив ссылок на работы портфолио для карты сайта
	 * @return array|null
	 */
	public static function getSiteMap() {

		$currentHost = \Difra\Envi::getHost();
		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `uri` FROM `portfolio_entry`";
		$res = $db->fetch( $query );
		$returnArray = array();
		if( !empty( $res ) ) {
			foreach( $res as $k=>$data ) {
				$returnArray[] = array( 'loc' => 'http://' . $currentHost . '/portfolio/' . $data['uri'] );
			}
		}
		return !empty( $returnArray ) ? $returnArray : null;
	}

	/**
	 * Возвращает в xml последние пять работ
	 * @param \DOMNode $node
	 * @param int      $limit
	 * @param int      $picLimit
	 */
	public static function getLastWorksXML( \DOMNode $node, $limit = 5, $picLimit = 3 ) {

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `name`, `uri`, `description`, `id` FROM `portfolio_entry` ORDER BY `release` DESC LIMIT " . intval( $limit );
		$res = $db->fetch( $query );
		if( !empty( $res ) ) {

			$idArray = array();
			foreach( $res as $k=>$data ) {
				$idArray[] = $data['id'];
			}
			//  забираем картинки работ
			$query = "SELECT `id`, `portfolio` FROM `portfolio_images` WHERE `portfolio` IN (" . implode( ', ', $idArray ) . ") ORDER BY `position` ASC";
			$imgRes = $db->fetch( $query );
			$imagesArray = array();
			if( !empty( $imgRes ) ) {
				foreach( $imgRes as $k=>$data ) {
					$imagesArray[$data['portfolio']][] = $data['id'];
				}
			}

			// собираем xml
			foreach( $res as $k=>$data ) {

				$workNode = $node->appendChild( $node->ownerDocument->createElement( 'work' ) );
				foreach( $data as $key=>$value ) {
					$workNode->setAttribute( $key, $value );
				}
				if( isset( $imagesArray[$data['id']] ) && is_array( $imagesArray[$data['id']] ) ) {
					$maxPics = 0;
					foreach( $imagesArray[$data['id']] as $img ) {
						if( $maxPics < $picLimit ) {
							$imgNode = $workNode->appendChild( $node->ownerDocument->createElement( 'image' ) );
							$imgNode->setAttribute( 'id', $img );
						}
						$maxPics++;
					}
				}
			}
		}
	}
}