<?php

namespace Difra\Plugins\Announcements;

Class Announcement {

    private $id = null;
    private $user = null;
    private $group = null;

    private $title = null;
    private $link = null;
    private $shortDescription = null;
    private $description = null;

    private $eventDate = null;
    private $beginDate = null;
    private $endDate = null;
    private $modified = null;

    private $visible = 1;
    private $priority = 50;
    private $statusInDays = null;

    private $userData = null;
    private $groupData = null;

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

        $this->title = $data['title'];
        $this->link = $data['link'];
        $this->shortDescription = $data['shortDescription'];
        $this->description = $data['description'];

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
    }

    /**
     * Создаёт объект анонса события и достаёт данные из базы по его ID
     * @static
     * @param $id
     */
    public static function getById( $id ) {

        $db = \Difra\MySQL::getInstance();
        $groupJoin = $groupSelect = '';

        if( \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {
            $groupSelect = ", g.`name`, g.`domain` ";
            $groupJoin = " LEFT JOIN `groups` AS `g` ON g.`id`=an.`group` ";
        }

        $query = "SELECT an.*, u.`email`, uf.`value` AS `nickname` " . $groupSelect . "
                    FROM `announcements` an
                    LEFT JOIN `users` AS `u` ON u.`id`=an.`user`
                    LEFT JOIN `users_fields` AS `uf` ON uf.`id`=an.`user` AND uf.`name`='nickname'
                    " . $groupJoin . "
                    WHERE an.`id`='" . intval( $id ) . "'";
        $res = $db->fetchRow( $query );
        if( !empty( $res ) ) {

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
     * @param $id
     */
    public function setId( $id ) {

        $this->id = intval( $id );
    }

    /**
     * Устанавливает id пользователя
     * @param int $userId
     */
    public function setUser( $userId ) {

        $this->user = $userId;
    }

    /**
     * Устанавливает id группы
     * @param int $groupId
     */
    public function setGroup( $groupId ) {

        $this->group = $groupId;
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
     * Устанавливает описание ивента
     * @param string $string
     */
    public function setDescription( $string ) {

        $this->description = trim( $string );
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

            $query = "INSERT INTO `announcements` SET `user`='" . $this->user . "', `group`='" . $this->group . "',
                        `title`='" . $db->escape( $this->title ) . "', `link`='" . $db->escape( $this->link ) . "',
                        `description`='" . $db->escape( $this->description ) . "', `shortDescription`='" .
                        $db->escape( $this->shortDescription ) . "', `eventDate`='" . $this->eventDate . "', `beginDate`='" .
                        $this->beginDate . "', `endDate`='" . $this->endDate . "', `visible`='" . $this->visible .
                        "', `priority`='" . $this->priority . "'";
        } else {

            $query = "UPDATE `announcements` SET `user`='" . $this->user . "', `group`='" . $this->group . "',
                        `title`='" . $db->escape( $this->title ) . "', `link`='" . $db->escape( $this->link ) . "',
                        `description`='" . $db->escape( $this->description ) . "',
                        `shortDescription`='" . $db->escape( $this->shortDescription ) . "', `eventDate`='" . $this->eventDate . "',
                        `beginDate`='" . $this->beginDate . "', `endDate`='" . $this->endDate . "', `visible`='" . $this->visible . "',
                        `priority`='" . $this->priority . "' WHERE `id`='" . intval( $this->id ) . "'";
        }
        $db->query( $query );
        if( is_null( $this->id ) ) {
            $this->id = $db->getLastId();
        }
    }

    /**
     * Возвращает массив объектов со всеми анонсами событий
     * @static
     * @param bool $onlyVisible
     */
    public static function getAll( $onlyVisible = false ) {

        $db = \Difra\MySQL::getInstance();
        $where = $groupJoin = $groupSelect = '';
        if( $onlyVisible ) {
            $where = " WHERE an.`visible`=1 ";
        }

        if( \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {
            $groupSelect = ", g.`name`, g.`domain` ";
            $groupJoin = " LEFT JOIN `groups` AS `g` ON g.`id`=an.`group` ";
        }

        $query = "SELECT an.*, u.`email`, uf.`value` AS `nickname` " . $groupSelect . "
                    FROM `announcements` an
                    LEFT JOIN `users` AS `u` ON u.`id`=an.`user`
                    LEFT JOIN `users_fields` AS `uf` ON uf.`id`=an.`user` AND uf.`name`='nickname'
                    " . $groupJoin . "
                    " . $where . "
                    ORDER BY an.`eventDate`, an.`priority` ASC";
        $res = $db->fetch( $query );
        $eventsArray = array();

        foreach( $res as $k=>$data ) {

            $event = new self;
            $event->setObject( $data );

            $eventsArray[$data['id']] = $event;
        }

        return $eventsArray;
    }

    /**
     * Возвращает массив объектов актуальных анонсов событий, выбранных по приоритету или больше его
     * @static
     * @param int $priority
     */
    public static function getByPriority( $priority = 100 ) {

        $db = \Difra\MySQL::getInstance();
        $groupJoin = $groupSelect = '';

        if( \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {
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
            foreach( $res as $k=>$data ) {

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
     * @param $groupId
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
     * @param \DOMNode $node
     */
    public function getXML( $node ) {

        $Locale = \Difra\Locales::getInstance();

        $eventNode = $node->appendChild( $node->ownerDocument->createElement( 'event' ) );

        $eventNode->appendChild( $node->ownerDocument->createElement( 'id', $this->id ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'user', $this->user ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'group', $this->group ) );

        $eventNode->appendChild( $node->ownerDocument->createElement( 'title', $this->title ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'link', $this->id . '-' . $this->link ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'shortDescription', $this->shortDescription ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'description', $this->description ) );

        $eventNode->appendChild( $node->ownerDocument->createElement( 'eventDate',
            $Locale->getDateFromMysql( $this->eventDate . ' 00:00:00' ) ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'beginDate',
            $Locale->getDateFromMysql( $this->beginDate . ' 00:00:00' ) ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'endDate',
            $Locale->getDateFromMysql( $this->endDate . ' 00:00:00' ) ) );

        $eventNode->appendChild( $node->ownerDocument->createElement( 'visible', $this->visible ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'priority', $this->priority ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'modified', $Locale->getDateFromMysql( $this->modified, true ) ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'status', $this->getStatus() ) );
        $eventNode->appendChild( $node->ownerDocument->createElement( 'statusInDays', $this->statusInDays ) );

        $userNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'userData' ) );

        if( !empty( $this->userData ) ) {
            foreach( $this->userData as $k=>$value ) {
                $userNode->setAttribute( $k, $value );
            }
        }

        if( \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) && !empty( $this->groupData ) ) {
            $groupNode = $eventNode->appendChild( $node->ownerDocument->createElement( 'groupData' ) );
            foreach( $this->groupData as $k=>$value ) {
                $groupNode->setAttribute( $k, $value );
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

        if( $diffDays<0 ) {
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

    /**
     * Возвращает id анонса
     * @return int
     */
    public function getId() {

        return $this->id;
    }
}