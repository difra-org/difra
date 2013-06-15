<?php

namespace Difra\Plugins;

class Portfolio {

	public $settings = null;

	public static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	private function __construct() {

		$this->_getSettings();
	}

	private function _getSettings() {

		$this->settings = \Difra\Config::getInstance()->get( 'portfolio-settings' );
	}

	public function saveContributor( $data ) {

		$archive = 0;
		if( isset( $data['archive'] ) && intval( $data['archive'] ) == 1 ) {
			$archive = 1;
		}

		Portfolio\Contributors::saveContributor( $data['user'],
			$data['name'],
			$data['linktext'],
			null,
			$data['role'],
			$archive );
	}

	/**
	 * @param \DOMElement $node
	 */
	public function getContributorsXML( $node ) {

		$data = Portfolio\Contributors::getContributors();
		foreach( $data as $user ) {
			/** @var \DOMElement $userNode */
			$userNode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
			foreach( $user as $k => $v ) {
				$userNode->setAttribute( $k, $v );
			}
		}
	}

	/**
	 * Удаляет юзера портфолио
	 * @param $id
	 */
	public function delContributor( $id ) {

		Portfolio\Contributors::delContributor( $id );
	}

	/**
	 * @param int         $id
	 * @param \DOMElement $node
	 * @return bool
	 */
	public function getContributorXML( $id, $node ) {

		$data = Portfolio\Contributors::getContributor( $id );
		if( $data ) {
			foreach( $data as $k => $v ) {
				$node->setAttribute( $k, $v );
			}
			return true;
		}
		return false;
	}

	private function _makeLink( $string ) {

		$link = '';
		$num = preg_match_all( '/[A-Za-zА-Яа-я0-9]*/u', $string, $matches );
		if( $num and !empty( $matches[0] ) ) {
			$matches = array_filter( $matches[0], 'strlen' );
			$link = implode( '-', $matches );
		}
		if( $link == '' ) {
			$link = '-';
		}
		return $link;
	}

	public function addWork( $data ) {

		$db = \Difra\MySQL::getInstance();

		$query = "INSERT INTO `portfolio_works` SET `name`='" . $db->escape( $data['name'] ) .
			"', `name_link`='" . $this->_makeLink( $db->escape( $data['name'] ) ) . "', `release_date`='" . $db->escape( $data['date'] ) . "'";

		if( isset( $data['workurl'] ) && $data['workurl'] != '' ) {
			$query .= ", `url`='" . $db->escape( $data['workurl'] ) . "'";
			if( isset( $data['linkText'] ) && $data['linkText'] != '' ) {
				$query .= ", `url_text`='" . $db->escape( $data['linkText'] ) . "'";
			}
		}
		if( isset( $data['software'] ) && $data['software'] != '' ) {
			$query .= ", `software`='" . $db->escape( $data['software'] ) . "'";
		}

		$db->query( $query );
		$workId = $db->getLastId();

		// сохраняем картинки из описания, если таковые имеются и апдейтим запись.
		if( isset( $data['description'] ) && !is_null( $data['description'] ) ) {

			if( $data['description'] instanceof \Difra\Param\AjaxHTML or $data['description'] instanceof \Difra\Param\AjaxSafeHTML ) {
				$data['description']->saveImages( DIR_DATA . 'portfolio/images/' . $workId, '/portimages/' . $workId );
			}

			$desc = $data['description']->val();
			$query = "UPDATE `portfolio_works` SET `description`='" . $db->escape( $desc ) . "' WHERE `id`='" . $workId . "'";
			$db->query( $query );
		}

		if( isset( $data['users'] ) && $data['users'] != '' ) {
			Portfolio\Contributors::saveWorkContributors( $workId, $data['users'], $data['userRole'] );
		}

		return $workId;
	}

	/**
	 * Сохраняет картинки работы портфолио
	 * @param      $workId
	 * @param      $mainImage
	 * @param null $previewImage
	 */
	public function saveImages( $workId, $mainImage, $previewImage = null ) {

		$savePath = DIR_DATA . 'portfolio/images/';
		@mkdir( $savePath, 0777, true );

		$Images = \Difra\Libs\Images::getInstance();

		// save original
		$newImg = $Images->convert( $mainImage, 'jpg' );
		if( file_put_contents( $savePath . 'portfolio-' . $workId . '-original.jpg', $newImg ) === false ) {
			throw new \Difra\Exception( 'Can\'t save image file.' );
		}

		// save previews
		if( !is_null( $previewImage ) ) {
			$smallImg = $Images->createThumbnail( $previewImage, $this->settings['thumb_maxWidth'], $this->settings['thumb_maxHeight'], 'jpg' );
			$largeImg = $Images->createThumbnail( $mainImage, $this->settings['thumb_maxWidth'] + 350, $this->settings['thumb_maxHeight'] + 750, 'jpg' );
		} else {
			$smallImg = $Images->createThumbnail( $mainImage, $this->settings['thumb_maxWidth'], $this->settings['thumb_maxHeight'], 'jpg' );
			$largeImg = $Images->createThumbnail( $mainImage, $this->settings['thumb_maxWidth'] + 350, $this->settings['thumb_maxHeight'] + 750, 'jpg' );
		}

		if( file_put_contents( $savePath . 'portfolio-' . $workId . '-small.jpg', $smallImg ) === false ) {
			throw new \Difra\Exception( 'Can\'t save image file.' );
		}
		if( file_put_contents( $savePath . 'portfolio-' . $workId . '-large.jpg', $largeImg ) === false ) {
			throw new \Difra\Exception( 'Can\'t save image file.' );
		}
	}

	/**
	 * Сохраняет отдельную картинку для превью работы
	 * @param $workId
	 * @param $previewImage
	 */
	public function savePreviewImage( $workId, $previewImage ) {

		$savePath = DIR_DATA . 'portfolio/images/';
		@mkdir( $savePath, 0777, true );

		$smallImg = \Difra\Libs\Images::getInstance()->createThumbnail( $previewImage,
			$this->settings['thumb_maxWidth'],
			$this->settings['thumb_maxHeight'],
			'jpg' );

		if( file_put_contents( $savePath . 'portfolio-' . $workId . '-small.jpg', $smallImg ) === false ) {
			throw new \Difra\Exception( 'Can\'t save image file.' );
		}

	}

	/**
	 * @param \DOMElement $node
	 * @param bool|int    $limit
	 */
	public function getPortfolioPreviewXML( $node, $limit = false ) {

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `id`, `name`, `release_date`, `name_link` 
								FROM `portfolio_works` ORDER BY `release_date` DESC";

		if( $limit ) {
			$query .= " LIMIT " . intval( $limit );
		}

		$res = $db->fetch( $query );

		$users = Portfolio\Contributors::getContributorsToWork();
		foreach( $res as $data ) {
			/** @var \DOMElement $itemNode */
			$itemNode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
			foreach( $data as $key => $value ) {
				$itemNode->setAttribute( $key, $value );
			}
			// юзеры
			if( isset( $users[$data['id']] ) ) {
				foreach( $users[$data['id']] as $uData ) {
					/** @var \DOMElement $userNode */
					$userNode = $itemNode->appendChild( $node->ownerDocument->createElement( 'user' ) );
					foreach( $uData as $uKey => $uValue ) {
						$userNode->setAttribute( $uKey, $uValue );
					}
				}
			}
		}
	}

	/**
	 * Удаляет работу из портфолио, а так же удаляет все картинки связанные с ней
	 * @param $id
	 */
	public function delete( $id ) {

		$db = \Difra\MySQL::getInstance();

		// удаляем папку с дополнительными
		$path = DIR_DATA . '/portfolio/images/' . $id;
		if( is_dir( $path ) ) {
			$dir = opendir( $path );
			if( $dir !== false ) {
				while( $file = readdir( $dir ) ) {
					if( is_file( $path . '/' . $file ) ) {
						unlink( $path . '/' . $file );
					}
				}
				closedir( $dir );
				rmdir( $path );
			}
		}

		// удаляем картинки
		@unlink( DIR_DATA . '/portfolio/images/portfolio-' . $id . '-large.jpg' );
		@unlink( DIR_DATA . '/portfolio/images/portfolio-' . $id . '-original.jpg' );
		@unlink( DIR_DATA . '/portfolio/images/portfolio-' . $id . '-small.jpg' );

		// удаляем из базы
		$db->query( 'DELETE FROM `portfolio_works` WHERE `id`=' . intval( $id ) );

	}

	/**
	 * @param int         $id
	 * @param \DOMElement $node
	 * @return bool
	 */
	public function getWorkXML( $id, $node ) {

		$db = \Difra\MySQL::getInstance();
		$res = $db->fetchRow( "SELECT * FROM `portfolio_works` WHERE `id`='" . intval( $id ) . "'" );
		if( empty( $res ) ) {
			return false;
		}
		foreach( $res as $key => $value ) {
			$node->appendChild( $node->ownerDocument->createElement( $key, $value ) );
		}

		$users = Portfolio\Contributors::getContributorsToWork( $id );
		if( isset( $users[$id] ) ) {
			$usersNode = $node->appendChild( $node->ownerDocument->createElement( 'users' ) );
			foreach( $users[$id] as $data ) {
				/** @var \DOMElement $userItem */
				$userItem = $usersNode->appendChild( $node->ownerDocument->createElement( 'item' ) );
				foreach( $data as $uKey => $uValue ) {
					$userItem->setAttribute( $uKey, $uValue );
				}
			}
		}

		return true;
	}

	public function update( $workId, $data ) {

		$db = \Difra\MySQL::getInstance();
		$query = "UPDATE `portfolio_works` SET `name`='" . $db->escape( $data['name'] ) .
			"', `name_link`='" . $this->_makeLink( $db->escape( $data['name'] ) ) . "', `release_date`='" . $db->escape( $data['date'] ) . "'";

		if( isset( $data['description'] ) && !is_null( $data['description'] ) ) {

			// save картинок эдитора
			if( $data['description'] instanceof \Difra\Param\AjaxHTML or $data['description'] instanceof \Difra\Param\AjaxSafeHTML ) {
				$data['description']->saveImages( DIR_DATA . 'portfolio/images/' . $workId, '/portimages/' . $workId );
			}

			$query .= ", `description`='" . $db->escape( $data['description']->val() ) . "'";
		}

		if( isset( $data['workurl'] ) && $data['workurl'] != '' ) {
			$query .= ", `url`='" . $db->escape( $data['workurl'] ) . "'";
			if( isset( $data['linktext'] ) && $data['linktext'] != '' ) {
				$query .= ", `url_text`='" . $db->escape( $data['linktext'] ) . "'";
			}
		}
		if( isset( $data['software'] ) ) {
			$query .= ", `software`='" . $db->escape( $data['software'] ) . "'";
		}

		$query .= " WHERE `id`='" . intval( $workId ) . "'";
		$db->query( $query );

		if( isset( $data['users'] ) && $data['users'] != '' ) {
			Portfolio\Contributors::saveWorkContributors( $workId, $data['users'], $data['userRole'] );
		}
	}

	public function saveSettings( $data ) {

		\Difra\Config::getInstance()->set( 'portfolio-settings', $data );
	}

	/**
	 * Устанавливаем xml с портфолио по годам выхода работ.
	 *
	 * @param \DOMElement $node
	 */
	public function getPortfolioByYearsXML( $node ) {

		$db = \Difra\MySQL::getInstance();

		$query = "SELECT `id`, `name_link`, `release_date`, DATE_FORMAT( `release_date`, '%Y')  AS `year`
					FROM `portfolio_works`
					ORDER BY `release_date` DESC";
		$res = $db->fetch( $query );

		if( !empty( $res ) ) {

			// строим массивчеки	
			$yearsArray = array();
			$worksArray = array();
			foreach( $res as $data ) {
				if( !in_array( $data['year'], $yearsArray ) ) {
					$yearsArray[] = $data['year'];
				}
				$worksArray[$data['year']][] = $data;
			}

			// строим xml'ку

			$yearsXml = $node->appendChild( $node->ownerDocument->createElement( 'years' ) );
			foreach( $yearsArray as $year ) {
				/** @var \DOMElement $yearItem */
				$yearItem = $yearsXml->appendChild( $node->ownerDocument->createElement( 'year' ) );
				$yearItem->setAttribute( 'year', $year );
			}

			$itemsXml = $node->appendChild( $node->ownerDocument->createElement( 'items' ) );
			foreach( $worksArray as $year => $items ) {
				/** @var \DOMElement $inYearXml */
				$inYearXml = $itemsXml->appendChild( $node->ownerDocument->createElement( 'year' ) );
				$inYearXml->setAttribute( 'year', $year );
				foreach( $items as $data ) {
					/** @var \DOMElement $portfolioItem */
					$portfolioItem = $inYearXml->appendChild( $node->ownerDocument->createElement( 'item' ) );
					foreach( $data as $key => $value ) {
						$portfolioItem->setAttribute( $key, $value );
					}

				}
			}

		} else {
			$node->appendChild( $node->ownerDocument->createElement( 'empty' ) );
		}
	}

	/**
	 * xml работы по её ссылке
	 *
	 * @param string      $link
	 * @param \DOMElement $node
	 * @return bool
	 */
	public function getWorkByLinkXML( $link, $node ) {

		$db = \Difra\MySQL::getInstance();
		$res = $db->fetchRow( "SELECT * FROM `portfolio_works` WHERE `name_link`='" . $db->escape( $link ) . "'" );
		if( empty( $res ) ) {
			return false;
		}

		foreach( $res as $key => $value ) {
			$node->appendChild( $node->ownerDocument->createElement( $key, $value ) );
		}

		$users = Portfolio\Contributors::getContributorsToWork( $res['id'] );
		if( isset( $users[$res['id']] ) ) {
			$usersNode = $node->appendChild( $node->ownerDocument->createElement( 'users' ) );
			foreach( $users[$res['id']] as $data ) {
				/** @var \DOMElement $userItem */
				$userItem = $usersNode->appendChild( $node->ownerDocument->createElement( 'item' ) );
				foreach( $data as $uKey => $uValue ) {
					$userItem->setAttribute( $uKey, $uValue );
				}
			}
		}
		return true;
	}

}
