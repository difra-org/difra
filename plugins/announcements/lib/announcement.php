<?php

namespace Difra\Plugins\Announcements;

use Difra\Envi;
use Difra\Plugger;

Class Announcement {

	private $id = null;
	private $user = null;
	private $group = null;
	private $category = null;
	private $location = null;
	private $locationData = null;

	private $title = null;
	private $link = null;
	private $shortDescription = null;
	private $description = null;

	private $fromEventDate = null;
	private $eventDate = null;
	private $beginDate = null;
	private $endDate = null;
	private $modified = null;

	private $visible = 1;
	private $priority = 50;
	private $statusInDays = null;

	private $userData = null;
	private $groupData = null;
	private $additionalData = null;
	private $schedule = null;

	public static function create() {

		return new self;
	}

	/**
	 * Устанавливает параметры объекта анонса событий
	 * @param array $data
	 */
	private function setObject( $data ) {

		$this->id = $data['id'];
		$this->user = $data['user'];
		$this->group = $data['group'];
		$this->category = $data['category'];
		$this->location = $data['location'];

		$this->title = $data['title'];
		$this->link = $data['link'];
		$this->shortDescription = $data['shortDescription'];
		$this->description = $data['description'];

		$this->fromEventDate = $data['fromEventDate'];
		$this->eventDate = $data['eventDate'];
		$this->beginDate = $data['beginDate'];
		$this->endDate = $data['endDate'];

		$this->visible = $data['visible'];
		$this->priority = $data['priority'];
		$this->modified = $data['modified'];

		if( isset( $data['domain'] ) ) {
			$this->groupData = array( 'name' => $data['name'], 'domain' => $data['domain'], 'id' => $data['group'] );
		}

		if( isset( $data['email'] ) ) {
			$this->userData = array( 'id' => $data['user'], 'email' => $data['email'], 'nickname' => $data['nickname'] );
		}

		if( isset( $data['schedule'] ) && $data['schedule'] != '' ) {
			$this->schedule = unserialize( $data['schedule'] );
		}

		if( isset( $data['additionalData'] ) && !empty( $data['additionalData'] ) ) {
			$additionalArray = array();

			foreach( $data['additionalData'] as $k => $addData ) {
				$additionalArray[$addData['additional_id']] = array( 'value' => $addData['value'], 'alias' => $addData['alias'] );
			}
			$this->additionalData = $additionalArray;
		}

		if( isset( $data['locationData'] ) && !empty( $data['locationData'] ) ) {
			$this->locationData = $data['locationData'];
		}
	}

	/**
	 * Создаёт объект анонса события и достаёт данные из базы по его ID
	 * @static
	 *
	 * @param $id
	 */
	public static function getById( $id ) {

		$db = \Difra\MySQL::getInstance();
		$groupJoin = $groupSelect = '';

		// Plugger::isEnabled( 'blogs' )

		if( Plugger::isEnabled( 'blogs' ) ) {
			$groupSelect = ", g.`name`, g.`domain` ";
			$groupJoin = " LEFT JOIN `groups` AS `g` ON g.`id`=an.`group` ";
		}

		$query = "SELECT an.*, u.`email`, uf.`value` AS `nickname`, anns.`schedule`, aloc.`locationData` " . $groupSelect . "
                    FROM `announcements` an
                    LEFT JOIN `users` AS `u` ON u.`id`=an.`user`
                    LEFT JOIN `users_fields` AS `uf` ON uf.`id`=an.`user` AND uf.`name`='nickname'
                    LEFT JOIN `announcements_schedules` AS `anns` ON anns.`announce_id`=an.`id`
                    LEFT JOIN `anouncements_locations` AS `aloc` ON an.`location`=aloc.`id`
                    " . $groupJoin . "
                    WHERE an.`id`='" . intval( $id ) . "'";
		$res = $db->fetchRow( $query );
		if( !empty( $res ) ) {

			// дополнительные поля
			$query = "SELECT adata.`additional_id`, adata.`value`, aa.`alias`
                        FROM `announcements_additionals_data` adata
                        LEFT JOIN `announcements_additionals` AS `aa` ON adata.`additional_id` = aa.`id`
                        WHERE adata.`announce_id`='" . intval( $id ) . "'";
			$addData = $db->fetch( $query );

			if( !empty( $addData ) ) {
				$res['additionalData'] = $addData;
			}
			if( isset( $res['locationData'] ) && $res['locationData'] != '' ) {
				$res['locationData'] = unserialize( $res['locationData'] );
			}

			$eventObject = new self;
			$eventObject->setObject( $res );

			return $eventObject;
		} else {
			return false;
		}
	}

	/**
	 * Устанавливает id анонса события.
	 * Нужен для апдейта данных в базе.
	 *
	 * @param $id
	 */
	public function setId( $id ) {

		$this->id = intval( $id );
	}

	/**
	 * Устанавливает id пользователя
	 *
	 * @param int $userId
	 */
	public function setUser( $userId ) {

		$this->user = $userId;
	}

	/**
	 * Устанавливает id группы
	 *
	 * @param int $groupId
	 */
	public function setGroup( $groupId ) {

		$this->group = $groupId;
	}

	public function setCategory( $category ) {

		$this->category = intval( $category );
	}

	/**
	 * Устанавливает название ивента
	 * @param string $string
	 */
	public function setTitle( $string ) {

		$this->title = trim( $string );
		$this->link = \Difra\Locales::getInstance()->makeLink( trim( $string ) );
	}

	/**
	 * @param $id
	 */
	public function setLocation( $id ) {

		$this->location = intval( $id );
	}

	/**
	 * Устанавливает описание ивента
	 * @param string $string
	 */
	public function setDescription( $string ) {

		$this->description = $string;
	}

	/**
	 * Устанавливает короткое описание ивента
	 * @param $string
	 */
	public function setShortDescription( $string ) {

		$this->shortDescription = trim( $string );
	}

	/**
	 * Устанавливает приоритет анонса
	 * @param int $value
	 */
	public function setPriority( $value ) {

		$this->priority = intval( $value );
	}

	/**
	 * Устанавливает видимость анонса
	 * @param int $value
	 */
	public function setVisible( $value ) {

		$this->visible = intval( $value );
	}

	/**
	 * Устанавливает дату начала события
	 * @param $date
	 */
	public function setFromEventDate( $date ) {

		$this->fromEventDate = \Difra\Locales::getInstance()->getMysqlDate( $date );
	}

	/**
	 * Устанавливает дату события
	 * @param string $date
	 */
	public function setEventDate( $date ) {

		$this->eventDate = \Difra\Locales::getInstance()->getMysqlDate( $date );
		$this->endDate = $this->eventDate;
	}

	/**
	 * Устанавливает дату начала демонстрации анонса
	 * @param string $date
	 */
	public function setBeginDate( $date ) {

		$this->beginDate = \Difra\Locales::getInstance()->getMysqlDate( $date );
	}

	/**
	 * Устанавливает дату окончания демонстрации анонса
	 * @param $date
	 */
	public function setEndDate( $date ) {

		$this->endDate = \Difra\Locales::getInstance()->getMysqlDate( $date );
	}

	/**
	 * Сохраняет анонс события в БД
	 */
	public function save() {

		$db = \Difra\MySQL::getInstance();

		if( is_null( $this->id ) ) {

			$query = "INSERT INTO `announcements` SET `user`='" . $this->user . "', `group`='" . $this->group .
				"', `title`='" . $db->escape( $this->title ) . "', `link`='" . $db->escape( $this->link ) .
				"', `shortDescription`='" . $db->escape( $this->shortDescription ) . "', `eventDate`='" . $this->eventDate .
				"', `beginDate`='" . $this->beginDate . "', `endDate`='" . $this->endDate .
				"', `visible`='" . $this->visible . "', `priority`='" . $this->priority . "', `category`='" . $this->category .
				"', `fromEventDate`='" . $this->fromEventDate . "', `location`='" . intval( $this->location ) . "'";
		} else {

			$query = "UPDATE `announcements` SET `user`='" . $this->user . "', `group`='" . $this->group .
				"', `title`='" . $db->escape( $this->title ) . "', `link`='" . $db->escape( $this->link ) .
				"', `shortDescription`='" . $db->escape( $this->shortDescription ) . "', `eventDate`='" . $this->eventDate .
				"', `beginDate`='" . $this->beginDate . "', `endDate`='" . $this->endDate . "', `visible`='" . $this->visible .
				"', `priority`='" . $this->priority . "', `category`='" . $this->category .
				"', `fromEventDate`='" . $this->fromEventDate . "', `location`='" . intval( $this->location ) .
				"' WHERE `id`='" . intval( $this->id ) . "'";
		}

		$db->query( $query );
		if( is_null( $this->id ) ) {
			$this->id = $db->getLastId();
		}
		$this->saveDescription();
	}

	/**
	 * Сохраняет картинки и полное описание анонса
	 */
	private function saveDescription() {

		if( $this->description instanceof \Difra\Param\AjaxHTML or $this->description instanceof \Difra\Param\AjaxSafeHTML ) {
			$this->description->saveImages( DIR_DATA . 'announcements/img/' . $this->id, '/announcements-img/' . $this->id );
			$this->description = $this->description->val();
		}

		$db = \Difra\MySQL::getInstance();
		$query = "UPDATE `announcements` SET `description`='" . $db->escape( $this->description ) .
			"' WHERE `id`='" . intval( $this->id ) . "'";
		$db->query( $query );
	}

	/**
	 * Возвращает массив объектов со всеми анонсами событий
	 * @static
	 *
	 * @param bool $onlyVisible
	 * @param bool $archive
	 */
	public static function getAll( $onlyVisible = false, $archive = false, $limit = 40 ) {

		$db = \Difra\MySQL::getInstance();
		$where = $groupJoin = $groupSelect = '';
		if( $onlyVisible ) {
			$where = " WHERE an.`visible`=1 AND an.`beginDate` <= NOW() AND an.`endDate` >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00')";
		}
		if( $archive ) {
			$where = " WHERE an.`visible`=1 AND an.`beginDate`<=NOW() ";
		}


		if( Plugger::isEnabled( 'blogs' ) ) {
			$groupSelect = ", g.`name`, g.`domain` ";
			$groupJoin = " LEFT JOIN `groups` AS `g` ON g.`id`=an.`group` ";
		}

		$query = "SELECT an.*, u.`email`, uf.`value` AS `nickname`, aloc.`locationData` " . $groupSelect . "
                    FROM `announcements` an
                    LEFT JOIN `users` AS `u` ON u.`id`=an.`user`
                    LEFT JOIN `users_fields` AS `uf` ON uf.`id`=an.`user` AND uf.`name`='nickname'
                    LEFT JOIN `anouncements_locations` AS `aloc` ON an.`location`=aloc.`id`
                    " . $groupJoin . "
                    " . $where . "
                    ORDER BY (an.`endDate` >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00')) DESC, an.`fromEventDate` ASC, an.`priority` DESC LIMIT " .
			intval( $limit );

		$res = $db->fetch( $query );
		$eventsArray = false;

		if( !empty( $res ) ) {
			// массив id
			$idArray = array();
			foreach( $res as $k => $data ) {
				$idArray[] = $data['id'];
			}

			// получаем все дополнительные поля на список ивентов
			$addData = Additionals::getByIdArray( $idArray );

			foreach( $res as $k => $data ) {

				$event = new self;
				if( !empty( $addData ) && isset( $addData[$data['id']] ) ) {
					$data['additionalData'][] = $addData[$data['id']];
				}
				if( isset( $data['locationData'] ) && $data['locationData'] != '' ) {
					$data['locationData'] = unserialize( $data['locationData'] );
				}

				$event->setObject( $data );
				$eventsArray[$data['id']] = $event;
			}
		}

		return $eventsArray;
	}

	/**
	 * Возвращает все действующие анонсы в категории
	 * @static
	 *
	 * @param $categoryId
	 */
	public static function getByCategory( $categoryId, $limit = 3 ) {

		$db = \Difra\MySQL::getInstance();
		$where = " WHERE an.`category`='" . intval( $categoryId ) .
			"' AND an.`visible`=1 ";

		$query = "SELECT an.*, aloc.`locationData`
                    FROM `announcements` an
                    LEFT JOIN `anouncements_locations` AS `aloc` ON an.`location`=aloc.`id`
                    " . $where . "
                    ORDER BY an.`fromEventDate` ASC, an.`priority` DESC LIMIT " . intval( $limit );

		$res = $db->fetch( $query );
		$eventsArray = false;

		if( !empty( $res ) ) {

			$idArray = array();
			foreach( $res as $k => $data ) {
				$idArray[] = $data['id'];
			}
			$addData = Additionals::getByIdArray( $idArray );
			foreach( $res as $k => $data ) {

				$event = new self;
				if( !empty( $addData ) && isset( $addData[$data['id']] ) ) {
					$data['additionalData'][] = $addData[$data['id']];
				}
				if( isset( $data['locationData'] ) && $data['locationData'] != '' ) {
					$data['locationData'] = unserialize( $data['locationData'] );
				}

				$event->setObject( $data );
				$eventsArray[$data['id']] = $event;
			}
		}
		return $eventsArray;
	}

	/**
	 * Возвращает ивенты по категории с постраничником
	 * @param     $categoryId
	 * @param int $page
	 * @param int $limit
	 */
	public static function getByCategoryWithPager( $categoryId, $page = 1, $perPage = 40 ) {

		$db = \Difra\MySQL::getInstance();
		$groupJoin = $groupSelect = '';

		if( Plugger::isEnabled( 'blogs' ) ) {
			$groupSelect = ", g.`name`, g.`domain` ";
			$groupJoin = " LEFT JOIN `groups` AS `g` ON g.`id`=an.`group` ";
		}

		$query = "SELECT an.*, u.`email`, uf.`value` AS `nickname`, aloc.`locationData` " . $groupSelect . "
                    FROM `announcements` an
                    LEFT JOIN `users` AS `u` ON u.`id`=an.`user`
                    LEFT JOIN `users_fields` AS `uf` ON uf.`id`=an.`user` AND uf.`name`='nickname'
                    LEFT JOIN `anouncements_locations` AS `aloc` ON an.`location`=aloc.`id`
                    " . $groupJoin . "
                    WHERE an.`visible`=1 AND an.`beginDate`<=NOW() AND an.`category`='" . intval( $categoryId ) . "'
                    ORDER BY (an.`endDate` >= DATE_FORMAT(NOW(),'%Y-%m-%d 00:00:00')) DESC, an.`fromEventDate` ASC, an.`priority` DESC LIMIT " .
			intval( ( $page - 1 ) * $perPage ) . "," . intval( $perPage );

		$res = $db->fetch( $query );
		$eventsArray = false;

		if( !empty( $res ) ) {
			// массив id
			$idArray = array();
			foreach( $res as $k => $data ) {
				$idArray[] = $data['id'];
			}

			// получаем все дополнительные поля на список ивентов
			$addData = Additionals::getByIdArray( $idArray );

			foreach( $res as $k => $data ) {

				$event = new self;
				if( !empty( $addData ) && isset( $addData[$data['id']] ) ) {
					$data['additionalData'][] = $addData[$data['id']];
				}
				if( isset( $data['locationData'] ) && $data['locationData'] != '' ) {
					$data['locationData'] = unserialize( $data['locationData'] );
				}

				$event->setObject( $data );
				$eventsArray[$data['id']] = $event;
			}
		}
		return $eventsArray;
	}

	/**
	 * Возвращает массив объектов актуальных анонсов событий, выбранных по приоритету или больше его
	 * @static
	 *
	 * @param int $priority
	 */
	public static function getByPriority( $priority = 100 ) {

		$db = \Difra\MySQL::getInstance();
		$groupJoin = $groupSelect = '';

		if( Plugger::isEnabled( 'blogs' ) ) {
			$groupSelect = ", g.`name`, g.`domain` ";
			$groupJoin = " LEFT JOIN `groups` AS `g` ON g.`id`=an.`group` ";
		}

		$query = "SELECT an.*, u.`email`, uf.`value` AS `nickname` " . $groupSelect . "
                    FROM `announcements` an
                    LEFT JOIN `users` AS `u` ON u.`id`=an.`user`
                    LEFT JOIN `users_fields` AS `uf` ON uf.`id`=an.`user` AND uf.`name`='nickname'
                    " . $groupJoin . "
                    WHERE an.`priority`>='" . intval( $priority ) . "' AND an.`eventDate`>=NOW() AND an.`visible`=1 ORDER BY an.`eventDate` ASC";
		$res = $db->fetch( $query );

		$eventsArray = array();
		if( !empty( $res ) ) {
			foreach( $res as $k => $data ) {

				$event = new self;
				$event->setObject( $data );
				$eventsArray[$data['id']] = $event;
			}
		}

		return $eventsArray;
	}

	/**
	 * Возвращает массив объектов актуальных анонсов, выбранных по id группы
	 * @static
	 *
	 * @param      $groupId
	 * @param bool $withArchive
	 */
	public static function getByGroup( $groupId, $withArchive = false ) {

		$db = \Difra\MySQL::getInstance();
		$whereAddon = " AND an.`eventDate`>=NOW() ";

		if( $withArchive ) {
			$whereAddon = '';
		}

		$query = "SELECT an.*, u.`email`, uf.`value` AS `nickname`, g.`name`, g.`domain`
                    FROM `announcements` an
                    LEFT JOIN `users` AS `u` ON u.`id`=an.`user`
                    LEFT JOIN `users_fields` AS `uf` ON uf.`id`=an.`user` AND uf.`name`='nickname'
                    LEFT JOIN `groups` AS `g` ON g.`id`=an.`group`
                    WHERE an.`group`>='" . intval( $groupId ) . "' " . $whereAddon . " AND an.`visible`=1 ORDER BY an.`eventDate` ASC";
		$res = $db->fetch( $query );

		$eventsArray = array();
		if( !empty( $res ) ) {
			foreach( $res as $k => $data ) {

				$event = new self;
				$event->setObject( $data );
				$eventsArray[$data['id']] = $event;
			}
		}

		return $eventsArray;
	}

	/**
	 * Возвращает объект анонса события в xml
	 *
	 * @param \DOMNode $node
	 */
	public function getXML( $node ) {

		$Locale = \Difra\Locales::getInstance();

		$eventNode = $node->appendChild( $node->ownerDocument->createElement( 'event' ) );

		$eventNode->appendChild( $node->ownerDocument->createElement( 'id', $this->id ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'user', $this->user ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'group', $this->group ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'category', $this->category ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'location', $this->location ) );

		$eventNode->appendChild( $node->ownerDocument->createElement( 'title', $this->title ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'link', $this->id . '-' . $this->link ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'shortDescription', $this->shortDescription ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'description', $this->description ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'status', $this->getStatus() ) );

		if( !is_null( $this->fromEventDate ) && $this->fromEventDate != ''
			&& $this->fromEventDate != '0000-00-00 00:00:00' && $this->fromEventDate != 'null'
		) {

			$fromEventDate = $Locale->getDateFromMysql( $this->fromEventDate . ' 00:00:00' );
			$dateNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'fromEventDate', $fromEventDate ) );
			$this->reFormateDate( $dateNode, $this->fromEventDate );

			$eventNode->appendChild( $node->ownerDocument->createElement( 'fromToEventDiff', $this->getEventPeriodDays() ) );
		}

		$dateNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'eventDate',
			$Locale->getDateFromMysql( $this->eventDate . ' 00:00:00' ) ) );
		$this->reFormateDate( $dateNode, $this->eventDate );

		$dateNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'beginDate',
			$Locale->getDateFromMysql( $this->beginDate . ' 00:00:00' ) ) );
		$this->reFormateDate( $dateNode, $this->beginDate );

		$dateNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'endDate',
			$Locale->getDateFromMysql( $this->endDate . ' 00:00:00' ) ) );
		$this->reFormateDate( $dateNode, $this->endDate );

		$eventNode->appendChild( $node->ownerDocument->createElement( 'visible', $this->visible ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'priority', $this->priority ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'modified', $Locale->getDateFromMysql( $this->modified, true ) ) );
		$eventNode->appendChild( $node->ownerDocument->createElement( 'statusInDays', $this->statusInDays ) );

		$userNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'userData' ) );

		if( !empty( $this->userData ) ) {
			foreach( $this->userData as $k => $value ) {
				$userNode->setAttribute( $k, $value );
			}
		}

		$this->getAdditionalXML( $eventNode );
		$this->getScheduleXML( $eventNode );
		$this->getLocationXML( $eventNode );

		if( Plugger::isEnabled( 'blogs' ) && !empty( $this->groupData ) ) {
			$groupNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'groupData' ) );
			foreach( $this->groupData as $k => $value ) {
				$groupNode->setAttribute( $k, $value );
			}
		}

	}

	/**
	 * Добавляет к ноде данные о дополнительных полях анонса
	 * @param \DOMNode $node
	 */
	private function getAdditionalXML( $node ) {

		if( !empty( $this->additionalData ) ) {
			$additionalNode = $node->appendChild( $node->ownerDocument->createElement( 'additionals' ) );
			foreach( $this->additionalData as $aId => $value ) {
				$addItemNode = $additionalNode->appendChild( $node->ownerDocument->createElement( 'field' ) );
				$addItemNode->setAttribute( 'id', $aId );
				$addItemNode->setAttribute( 'value', $value['value'] );
				$addItemNode->setAttribute( 'alias', $value['alias'] );
			}
		}
	}

	/**
	 * Добавляет к ноде данные о месте проведения события
	 * @param \DOMNode $node
	 */
	private function getLocationXML( $node ) {

		if( !empty( $this->locationData ) ) {
			$locationNode = $node->appendChild( $node->ownerDocument->createElement( 'location-data' ) );
			foreach( $this->locationData as $k => $value ) {
				$locationNode->setAttribute( $k, $value );
			}
		}
	}

	/**
	 * Добавляет к ноде данные о расписаниях анонса
	 * @param \DOMNode $node
	 */
	private function getScheduleXML( $node ) {

		if( !empty( $this->schedule ) ) {
			$scheduleNode = $node->appendChild( $node->ownerDocument->createElement( 'schedules' ) );
			if( isset( $this->schedule['name'] ) && $this->schedule['name'] != '' ) {
				$scheduleNode->setAttribute( 'title', $this->schedule['name'] );
			}
			foreach( $this->schedule['schedule'] as $k => $data ) {
				$itemNode = $scheduleNode->appendChild( $node->ownerDocument->createElement( 'item' ) );
				foreach( $data as $n => $v ) {
					$itemNode->setAttribute( $n, $v );
				}
			}
		}
	}

	/**
	 * Возвращает текстовый статус анонса (прошел, сегодня, завтра и .т.д.)
	 * @return string
	 */
	public function getStatus() {

		$nowDate = new \DateTime( date( 'Y-m-d' ) );
		$eventDate = new \DateTime( $this->eventDate );

		$diffDays = $nowDate->diff( $eventDate );
		$this->statusInDays = $diffDays = intval( $diffDays->format( '%r%a' ) );

		if( $diffDays < 0 ) {
			return 'past';
		}
		if( $diffDays == 0 ) {
			return 'today';
		}
		if( $diffDays == 1 ) {
			return 'tomorrow';
		}
		if( $diffDays > 1 ) {
			return 'inFuture';
		}

		return null;
	}

	/** Возвращает кол-во дней в периоде события и сколько осталось до его начала
	 * @return int|null
	 */
	public function getEventPeriodDays() {

		if( !is_null( $this->fromEventDate ) && $this->fromEventDate != '' &&
			$this->fromEventDate != '0000-00-00 00:00:00' && $this->fromEventDate != 'null'
		) {

			$nowDate = new \DateTime( date( 'Y-m-d' ) );
			$fromDate = new \DateTime( $this->fromEventDate );
			$eventDate = new \DateTime( $this->eventDate );
			$diffDays = $fromDate->diff( $eventDate );

			$startDays = $nowDate->diff( $fromDate );
			$this->statusInDays = intval( $startDays->format( '%r%a' ) );

			return intval( $diffDays->format( '%r%a' ) );
		}
		return null;
	}

	/**
	 * Реформатирует дату и разбивает её на части для использования в шаблонах
	 * @param \DOMNode $node
	 * @param string   $date
	 */
	private function reFormateDate( $node, $date ) {

		$date = strtotime( $date );
		$node->setAttribute( 'd', date( 'j', $date ) );
		$node->setAttribute( 'm', date( 'm', $date ) );
		$node->setAttribute( 'y', date( 'y', $date ) );
		$node->setAttribute( 'w', date( 'w', $date ) );
	}

	/**
	 * Возвращает id анонса
	 * @return int
	 */
	public function getId() {

		return $this->id;
	}

	/**
	 * Возвращает заголовок анонса
	 * @return string
	 */
	public function getTitle() {

		return $this->title;
	}

	/** Возвращает дату последней модификации
	 * @return string
	 */
	public function getModified() {

		return $this->modified;
	}

	/**
	 * Возвращает полную ссылку на анонс
	 * @return string
	 */
	public function getLink() {

		$server = Envi::getHost();
		return 'http://' . $server . '/events/' . $this->id . '-' . $this->link;
	}

	/**
	 * Возвращает короткое описание
	 * @return string
	 */
	public function getShortDescription() {

		return $this->shortDescription;
	}

	/**
	 * Возвращает заголовок с датами проведения события в читабельном для человека виде
	 */
	public function getHumanizedTitle() {

		$Locale = \Difra\Locales::getInstance();
		$title = $this->title . '. ';

		if( isset( $this->locationData['name'] ) && $this->locationData['name'] != '' ) {
			$title .= $this->locationData['name'] . '. ';
		}

		if( $this->fromEventDate != '' && $this->fromEventDate != $this->eventDate ) {

			$title .= date( 'd', strtotime( $this->fromEventDate ) ) . ' ';

			$title .= $Locale->getXPath( "announcements/dates/months/*[name()='month_" .
					date( 'm', strtotime( $this->fromEventDate ) ) . "']" );

			$title .= $Locale->getXPath( 'announcements/fromTo' );
			$title .= date( 'd', strtotime( $this->eventDate ) ) . ' ';

			$title .= $Locale->getXPath( "announcements/dates/months/*[name()='month_" .
					date( 'm', strtotime( $this->eventDate ) ) . "']" );

		} else {
			// j
			$title .= $Locale->getXPath( "announcements/dates/weekdays/*[name()='day_" .
					date( 'w', strtotime( $this->eventDate ) ) . "']" ) . ', ';

			$title .= date( 'd', strtotime( $this->eventDate ) ) . ' ';
			$title .= $Locale->getXPath( "announcements/dates/months/*[name()='month_" .
					date( 'm', strtotime( $this->eventDate ) ) . "']" );
		}

		if( !empty( $this->additionalData ) ) {
			foreach( $this->additionalData as $k => $data ) {
				if( isset( $data['alias'] ) && $data['alias'] == 'eventTime' ) {
					$title .= $Locale->getXPath( 'announcements/in' );
					$title .= $data['value'];
				}
			}
		}
		$title .= '.';
		return $title;
	}

	/**
	 * Возвращает строку с "человечной" датой события
	 */
	public function getHumanizedDate() {

		$title = '';
		$Locale = \Difra\Locales::getInstance();
		if( $this->fromEventDate != '' && $this->fromEventDate != $this->eventDate ) {

			$title .= date( 'd', strtotime( $this->fromEventDate ) ) . ' ';
			$title .= $Locale->getXPath( "announcements/dates/months/*[name()='month_" .
					date( 'm', strtotime( $this->fromEventDate ) ) . "']" );
			$title .= $Locale->getXPath( 'announcements/fromTo' );
			$title .= date( 'd', strtotime( $this->eventDate ) ) . ' ';
			$title .= $Locale->getXPath( "announcements/dates/months/*[name()='month_" .
					date( 'm', strtotime( $this->eventDate ) ) . "']" );
		} else {
			// j
			$title .= $Locale->getXPath( "announcements/dates/weekdays/*[name()='day_" .
					date( 'w', strtotime( $this->eventDate ) ) . "']" ) . ', ';
			$title .= date( 'd', strtotime( $this->eventDate ) ) . ' ';
			$title .= $Locale->getXPath( "announcements/dates/months/*[name()='month_" .
					date( 'm', strtotime( $this->eventDate ) ) . "']" );
		}

		if( !empty( $this->additionalData ) ) {
			foreach( $this->additionalData as $k => $data ) {
				if( isset( $data['alias'] ) && $data['alias'] == 'eventTime' ) {
					$title .= $Locale->getXPath( 'announcements/in' );
					$title .= $data['value'];
				}
			}
		}

		return $title;
	}
}