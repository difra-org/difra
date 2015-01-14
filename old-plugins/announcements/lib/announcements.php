<?php

namespace Difra\Plugins;

use Difra\Envi;

class Announcements {

	/**
	 * Массив с конфигом и дефолтными значениями
	 * @var array
	 */
	private $settings = array( 'on' => 0, 'maxPerUser' => 5, 'maxPerGroup' => 5, 'width' => 200, 'height' => 180, 'perPage' => 40 );

	private $imagePath = null;

	static public function getInstance() {

		static $_self = null;
		return $_self ? $_self : $_self = new self;
	}

	public function __construct() {

		$this->_getSettings();
		$this->imagePath = DIR_DATA . 'announcements';
	}

	private function _getSettings() {

		$settings = \Difra\Config::getInstance()->get( 'announcements' );
		if( !empty( $settings ) ) {
			$this->settings = $settings;
		}
	}

	/**
	 * Возвращает настройки плагина в xml
	 *
	 * @param \DOMNode $node
	 */
	public function getSettingsXml( $node ) {

		foreach( $this->settings as $key => $value ) {
			$node->setAttribute( $key, $value );
		}
	}

	/**
	 * Сохраняет настройки плагина
	 * @param array $data
	 */
	public function saveSettings( $data ) {

		\Difra\Config::getInstance()->set( 'announcements', $data );
	}

	/**
	 * Сохраняет в нужном месте картинку анонса
	 * @param $id
	 * @param $fileData
	 */
	public function saveImage( $id, $fileData ) {

		@mkdir( $this->imagePath, 0777, true );

		if( !is_writeable( $this->imagePath ) ) {
			throw new \Difra\Exception( 'Directory is no writeable!' );
		}

		$Images = \Difra\Libs\Images::getInstance();

		$img = $fileData instanceof \Difra\Param\AjaxFile ? $fileData->val() : $fileData;

		try {
			$rawImg = $Images->data2image( $img );

			$newImg = $Images->scaleAndCrop( $rawImg, $this->settings['width'], $this->settings['height'], 'png' );
			$bigImg = $Images->scaleAndCrop( $rawImg, $this->settings['bigWidth'], $this->settings['bigHeight'], 'png' );

		} catch( \Difra\Exception $ex ) {
			throw new \Difra\Exception( 'Bad image format. ' . $ex->getMessage() );
		}

		try {
			file_put_contents( $this->imagePath . '/' . $id . '.png', $newImg );
			file_put_contents( $this->imagePath . '/' . $id . '-big.png', $bigImg );
		} catch( \Difra\Exception $ex ) {
			throw new \Difra\Exception( "Can't save image" );
		}
	}

	/**
	 * Создаёт или апдейтит анонс события и возвращает id
	 *
	 * @param array $data
	 */
	public function create( $data ) {

		$Event = \Difra\Plugins\Announcements\Announcement::create();
		$Event->setUser( $data['user'] );
		$Event->setGroup( $data['group'] );
		$Event->setCategory( $data['category'] );
		$Event->setTitle( $data['title'] );
		$Event->setShortDescription( $data['shortDescription'] );
		$Event->setDescription( $data['description'] );
		$Event->setLocation( $data['location'] );

		$Event->setFromEventDate( $data['fromEventDate'] );
		$Event->setEventDate( $data['eventDate'] );
		$Event->setBeginDate( $data['beginDate'] );

		if( isset( $data['priority'] ) && !is_null( $data['priority'] ) ) {
			$Event->setPriority( $data['priority'] );
		}
		if( isset( $data['visible'] ) && !is_null( $data['visible'] ) ) {
			$Event->setVisible( $data['visible'] );
		}
		if( isset( $data['endDate'] ) && !is_null( $data['endDate'] ) ) {
			$Event->setEndDate( $data['endDate'] );
		}
		if( isset( $data['id'] ) && $data['id'] != 0 ) {
			$Event->setId( $data['id'] );
		}

		$Event->save();

		return $Event->getId();
	}

	/**
	 * Возвращает в xml все анонсы событий
	 *
	 * @param \DOMNode $node
	 * @param bool     $onlyVisible
	 */
	public function getAllEventsXML( $node, $onlyVisible = false, $withArchive = false, $perPage = null ) {

		if( !is_null( $perPage ) ) {
			$perPageLimit = intval( $perPage );
		} else {
			$perPageLimit = \Difra\Config::getInstance()->getValue( 'announcements', 'perPage' );
		}

		if( empty( $perPageLimit ) || $perPageLimit == 0 ) {
			throw new \Difra\Exception( 'No page limit! Reconfigure Announcements plugin.' );
		}

		$events = \Difra\Plugins\Announcements\Announcement::getAll( $onlyVisible, $withArchive, $perPageLimit );
		if( !empty( $events ) ) {

			foreach( $events as $k => $object ) {
				$object->getXML( $node );
			}
		}
	}

	/**
	 * Возвращает массив объектов с анонсами
	 * @param bool $onlyVisible
	 */
	public function getAllEvents( $onlyVisible = false, $withArchive = false ) {

		$events = \Difra\Plugins\Announcements\Announcement::getAll( $onlyVisible, $withArchive, 40 );
		return $events;
	}

	/**
	 * Возвращает массив ссылок на анонсы для карты сайта
	 * @static
	 * @return array
	 */
	public static function getMap() {

		$db = \Difra\MySQL::getInstance();
		$where = " `visible`=1 ";
		$query = "SELECT `id`, `link`, UNIX_TIMESTAMP( `modified` ) AS `mod` FROM `announcements` WHERE " . $where . " ORDER BY `modified`";
		$res = $db->fetch( $query );

		$mainHost = Envi::getHost();

		$returnArray = array();
		foreach( $res as $k => $data ) {

			$link = 'http://' . $mainHost . '/events/' . $data['id'] . '-' . $data['link'];
			$date = date( 'Y-m-d', $data['mod'] );
			$returnArray[] = array( 'loc' => $link, 'lastmod' => $date );
		}

		return $returnArray;
	}

	/**
	 * Устанавливает приоритет анонса события
	 * @static
	 * @param $id
	 * @param $priority
	 */
	public static function setPriority( $id, $priority ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "UPDATE `announcements` SET `priority`='" . intval( $priority ) . "' WHERE `id`='" . intval( $id ) . "'" );
		return true;
	}

	/**
	 * Удаляет анонс события и все его картинки
	 * @param $id
	 * @return bool
	 */
	public function delete( $id ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `announcements` WHERE `id`='" . intval( $id ) . "'" );
		@unlink( $this->imagePath . '/' . intval( $id ) . '.png' );
		@unlink( $this->imagePath . '/' . intval( $id ) . '-big.png' );

		$path = DIR_DATA . 'announcements/img/' . intval( $id );
		if( is_dir( $path ) ) {
			$dir = opendir( $path );
			while( false !== ( $file = readdir( $dir ) ) ) {
				if( $file{0} == '.' ) {
					continue;
				}
				@unlink( "$path/$file" );
			}
			rmdir( $path );
		}
		return true;
	}

	/**
	 * Возвращает в xml данные анонса события
	 *
	 * @param int      $id
	 * @param \DOMNode $node
	 */
	public function getByIdXML( $id, $node ) {

		$eventObject = \Difra\Plugins\Announcements\Announcement::getById( $id );
		$eventObject->getXML( $node );
	}

	/**
	 * Проверяет возможности создания анонса для текущего юзера или группы
	 * @return bool
	 */
	public function checkCreateLimits() {

		$db = \Difra\MySQL::getInstance();
		$groupId = null;
		$userId = \Difra\Auth::getInstance()->getId();

		if( \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {

			$currentGroup = \Difra\Plugins\Blogs\Group::current();
			if( !is_null( $currentGroup ) ) {
				$groupId = $currentGroup->getId();
			}
		}

		if( !is_null( $groupId ) ) {

			$query = "SELECT COUNT(`id`) AS `idCount` FROM `announcements` WHERE `group`='" . intval( $groupId ) . "'";
			$res = $db->fetchRow( $query );
			if( $res['idCount'] >= $this->settings['maxPerGroup'] ) {
				return false;
			}
		} else {
			$query = "SELECT COUNT(`id`) AS `idCount` FROM `announcements` WHERE `user`='" . intval( $userId ) . "'";
			$res = $db->fetchRow( $query );
			if( $res['idCount'] >= $this->settings['maxPerUser'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Проверяет является ли пользователь владельцем анонса
	 * @param $eventId
	 * @param $userId
	 */
	public function checkOnwer( $eventId, $userId ) {

		if( \Difra\Auth::getInstance()->isModerator() ) {
			return true;
		}

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `id` FROM `announcements` WHERE `id`='" . intval( $eventId ) . "' AND `user`='" . intval( $userId ) . "'";
		$res = $db->fetchOne( $query );
		return !empty( $res ) ? true : false;
	}


	/**
	 * Возвращает в xml данные анонса события по его ссылке
	 * @deprecated
	 * @param string   $link
	 * @param \DOMNode $node
	 */
	public function getByLinkXML( $link, $node ) {

		// ищем в ссылке idшник
		$regs = explode( '-', $link );

		if( isset( $regs[0] ) && intval( $regs[0] ) != 0 ) {

			$Event = \Difra\Plugins\Announcements\Announcement::getById( intval( $regs[0] ) );
			if( $Event ) {

				$Event->getXML( $node );
				$node->parentNode->setAttribute( 'title', $Event->getHumanizedTitle() );
				return $Event;
			}
		}

		return false;
	}

	/**
	 * Возвращает объект анонса по ссылке
	 * @param $link
	 */
	public function getByLink( $link ) {

		// ищем в ссылке idшник
		$regs = explode( '-', $link );

		if( isset( $regs[0] ) && intval( $regs[0] ) != 0 ) {

			$Event = \Difra\Plugins\Announcements\Announcement::getById( intval( $regs[0] ) );
			if( $Event ) {
				return $Event;
			}
		}

		return false;
	}

	/**
	 * Возвращает в xml список анонсов событий для выбранного приоритета или больше его
	 *
	 * @param \DOMNode $node
	 * @param int      $priority
	 */
	public function getByPriorityXML( $node, $priority = 100 ) {

		$Events = \Difra\Plugins\Announcements\Announcement::getByPriority( $priority );
		if( !empty( $Events ) ) {
			foreach( $Events as $k => $obj ) {
				$obj->getXml( $node );
			}
		}
	}

	/**
	 * Возвращает текущие анонсы по их категории
	 * @param     $categoryId
	 * @param int $limit
	 */
	public function getByCategory( $categoryId, $limit = 3 ) {

		return $Events = \Difra\Plugins\Announcements\Announcement::getByCategory( $categoryId, $limit );
	}

	public function getByCategoryWithPagerXML( $categoryId, $node, $page = 1 ) {

		$db = \Difra\MySQL::getInstance();

		// считаем общее количество в категории
		$all = $db->fetchRow( "SELECT COUNT(`id`) AS `acount` FROM `announcements` WHERE `category`='" .
		intval( $categoryId ) . "' AND `visible`=1" );

		if( $all['acount'] > 0 ) {

			$perPage = $this->settings['perPage'];
			$pages = floor( ( $all['acount'] - 1 ) / $perPage ) + 1;
			$node->setAttribute( 'pages', $pages );
			$node->setAttribute( 'current', $page );
			$events = \Difra\Plugins\Announcements\Announcement::getByCategoryWithPager( $categoryId, $page, $perPage );
			if( !empty( $events ) ) {
				foreach( $events as $k => $object ) {
					$object->getXml( $node );
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * Возвращает в xml все события группы
	 *
	 * @param int      $groupId
	 * @param \DOMNode $node
	 * @param bool     $withArchive
	 */
	public function getByGroupXML( $groupId, $node, $withArchive = false ) {

		$Events = \Difra\Plugins\Announcements\Announcement::getByGroup( $groupId, $withArchive );
		if( !empty( $Events ) ) {
			foreach( $Events as $k => $obj ) {
				$obj->getXml( $node );
			}
		}
	}

	/**
	 * Добавляет категорию или обновляет категорию
	 * @param string $name
	 * @param int    $id
	 */
	public function saveCategory( $techAlias, $categoryName, $id = null ) {

		$Category = \Difra\Plugins\Announcements\Category::create( $id );
		$Category->setTextName( $categoryName );
		$Category->setCategory( $techAlias );
	}

	/**
	 * Проверяет есть ли уже такая категория
	 * @param $name
	 */
	public function checkCategoryName( $name ) {

		return \Difra\Plugins\Announcements\Category::checkName( $name );
	}

	public function saveAdditionalField( $name, $alias, $id = null ) {

		$A = \Difra\Plugins\Announcements\Additionals::create( $id );
		$A->setName( $name );
		$A->setAlias( $alias );
	}

	/**
	 * Сохраняет расписание
	 * @param       $id
	 * @param       $name
	 * @param array $names
	 * @param array $values
	 */
	public function saveSchedules( $id, $name, $names, $values ) {

		$db = \Difra\MySQL::getInstance();
		$scheduleArray = null;
		if( !empty( $names ) && !empty( $values ) ) {
			foreach( $names as $num => $data ) {
				if( isset( $values[$num] ) && $values[$num] != '' ) {
					$scheduleArray['schedule'][$num]['name'] = $data;
					$scheduleArray['schedule'][$num]['value'] = $values[$num];
				}
			}

			if( !empty( $scheduleArray ) ) {
				if( $name != '' ) {
					$scheduleArray['name'] = trim( $name );
				}
				$scheduleArray = serialize( $scheduleArray );
				$query = "INSERT INTO `announcements_schedules` (`announce_id`, `schedule`) VALUES ('" . intval( $id ) .
					"', '" . $db->escape( $scheduleArray ) . "') ON DUPLICATE KEY UPDATE `schedule` = '" . $db->escape( $scheduleArray ) .
					"'";
				$db->query( $query );
			}
		}
	}

	/**
	 * Возвращает локации в xml
	 *
	 * @param \DOMNode $node
	 */
	public function getLocationsXML( $node ) {

		$db = \Difra\MySQL::getInstance();
		$db->fetchXML( $node, "SELECT `id`, `name` FROM `anouncements_locations`" );
	}

	/**
	 * Добавляет или обновлят локацию
	 * @param array $data
	 * @param int   $id
	 */
	public function saveLocation( $data, $id = null ) {

		$db = \Difra\MySQL::getInstance();
		$saveArray = serialize( $data );

		if( !is_null( $id ) ) {
			$query = "UPDATE `anouncements_locations` SET `name`='" . $db->escape( $data['name'] ) .
				"', `locationData`='" . $db->escape( $saveArray ) . "' WHERE `id`='" . intval( $id ) . "'";
		} else {
			$query = "INSERT INTO `anouncements_locations` (`name`, `locationData`) VALUES ('" . $db->escape( $data['name'] ) . "', '" .
				$db->escape( $saveArray ) . "')";
		}

		$db->query( $query );
	}

	/**
	 * Удаляет локацию
	 * @param int $id
	 */
	public function deleteLocation( $id ) {

		\Difra\MySQL::getInstance()->query( "DELETE FROM `anouncements_locations` WHERE `id`='" . intval( $id ) . "'" );
	}

	/**
	 * Возвращает локацию по её id
	 *
	 * @param          $id
	 * @param \DOMNode $node
	 */
	public function getLocationByIdXML( $id, $node ) {

		$db = \Difra\MySQL::getInstance();
		$res = $db->fetchRow( "SELECT `locationData` FROM `anouncements_locations` WHERE `id`='" . intval( $id ) . "'" );
		if( !empty( $res ) ) {
			$data = unserialize( $res['locationData'] );
			foreach( $data as $k => $value ) {
				$node->setAttribute( $k, $value );
			}
		}
	}

	/**
	 * Возвращает владельца ивента из базы данных
	 * @param $eventId
	 */
	public function getOwner( $eventId ) {
		$db = \Difra\MySQL::getInstance();
		$query = "SELECT `user` FROM `announcements` WHERE `id`='" . intval( $eventId ) . "'";
		$res = $db->fetchOne( $query );
		return !empty( $res ) ? $res : false;
	}

	/**
	 * Возвращает заголовки и ссылки на анонсы для экспорта в сторонние социалочки
	 */
	public function getForExport() {

		$returnArray = array();
		$eventsArray = \Difra\Plugins\Announcements\Announcement::getForExport();
		if( !is_null( $eventsArray ) ) {

			foreach( $eventsArray as $key=>$Event ) {

				$title = $Event->getTitle();

				$link = 'http://' . Envi::getHost() . '/events/' . $Event->getId();

				if( mb_strlen( $title )>=130 ) {
					$title = mb_substr( $title, 0, 130 ) . '...';
				}

				$returnArray[$Event->getId()] = $title . ' ' . $link;
			}
		}

		return $returnArray;
	}

	/**
	 * Устанавливает флаг экспорта для массива id анонсов
	 * @param array $exportIds
	 */
	public function setExported( array $exportIds ) {

		$exportIds = array_map( 'intval', $exportIds );

		$db = \Difra\MySQL::getInstance();
		$query = "UPDATE `announcements` SET `exported`=1 WHERE `id` IN (" . implode( ', ', $exportIds ) . ")";
		$db->query( $query );
	}

}