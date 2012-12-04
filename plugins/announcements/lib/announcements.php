<?php

namespace Difra\Plugins;

class Announcements {

    /**
     * Массив с конфигом и дефолтными значениями
     * @var array
     */
    private $settings = array( 'on' => 0, 'maxPerUser' => 5, 'maxPerGroup' => 5, 'width' => 200, 'height' => 180 );

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
     * @param \DOMNode $node
     */
    public function getSettingsXml( $node ) {

        foreach( $this->settings as $key=>$value ) {
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

        @mkdir( $this->imagePath, 0755, true );

        $Images = \Difra\Libs\Images::getInstance();

        try {
            $rawImg = $Images->data2image( $fileData );
        } catch( \Difra\Exception $ex ) {
            throw new \Difra\Exception( 'Bad image format.' );
        }

        $newImg = $Images->createThumbnail( $rawImg, $this->settings['width'], $this->settings['height'], 'png' );

        try {
            file_put_contents( $this->imagePath . '/' . $id . '.png', $newImg );
        } catch( \Difra\Exception $ex ) {
            throw new \Difra\Exception( "Can't save image" );
        }
    }

    /**
     * Создаёт или апдейтит анонс события и возвращает id
     * @param array $data
     */
    public function create( $data ) {

        $Event = \Difra\Plugins\Announcements\Announcement::create();
        $Event->setUser( $data['user'] );
        $Event->setGroup( $data['group'] );
        $Event->setTitle( $data['title'] );
        $Event->setShortDescription( $data['shortDescription'] );
        $Event->setDescription( $data['description'] );

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
        if( isset( $data['id'] ) && $data['id']!=0 ) {
            $Event->setId( $data['id'] );
        }

        $Event->save();

        return $Event->getId();
    }


    /**
     * Возвращает в xml все анонсы событий
     * @param \DOMNode $node
     * @param bool $onlyVisible
     */
    public function getAllEventsXML( $node, $onlyVisible = false ) {

        $events = \Difra\Plugins\Announcements\Announcement::getAll( $onlyVisible );
        if( !empty( $events ) ) {

            foreach( $events as $k=>$object ) {
                $object->getXML( $node );
            }
        }
    }

    /**
     * Возвращает массив ссылок на анонсы для карты сайта
     * @static
     * @return array
     */
    public static function getMap() {

        $db = \Difra\MySQL::getInstance();
        $query = "SELECT `id`, `link`, UNIX_TIMESTAMP( `modified` ) AS `mod` FROM `announcements` WHERE `visible`=1 ORDER BY `modified`";
        $res = $db->fetch( $query );

        $mainHost = \Difra\Site::getInstance()->getMainhost();

        $returnArray = array();
        foreach( $res as $k=>$data ) {

            $link = 'http://' . $mainHost . '/event/' . $data['id'] . '-' . $data['link'];
            $date = date( 'c', $data['mod'] );
            $returnArray[] = array( 'link' => $link, 'lastmod' => $date );
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
        return true;
    }

    /**
     * Возвращает в xml данные анонса события
     * @param int $id
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
            if( $res['idCount']>=$this->settings['maxPerGroup'] ) {
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
     * Возвращает в xml данные анонса события по его ссылке
     * @param string $link
     * @param \DOMNode $node
     */
    public function getByLinkXML( $link, $node ) {

        // ищем в ссылке idшник
        preg_match( '/\\A(\\d+)-(?:.*)/', $link, $regs );
        if( isset( $regs[1] ) && intval( $regs[1] )!=0 ) {

            $Event = \Difra\Plugins\Announcements\Announcement::getById( intval( $regs[1] ) );
            if( $Event ) {

                $Event->getXML( $node );
                return true;
            }
        }

        return false;
    }

    /**
     * Возвращает в xml список анонсов событий для выбранного приоритета или больше его
     * @param \DOMNode $node
     * @param int $priority
     */
    public function getByPriorityXML( $node, $priority = 100 ) {

        $Events = \Difra\Plugins\Announcements\Announcement::getByPriority( $priority );
        if( !empty( $Events ) ) {
            foreach( $Events as $k=>$obj ) {
                $obj->getXml( $node );
            }
        }
    }

    /**
     * Возвращает в xml все события группы
     * @param int $groupId
     * @param \DOMNode $node
     * @param bool $withArchive
     */
    public function getByGroupXML( $groupId, $node, $withArchive = false ) {

        $Events = \Difra\Plugins\Announcements\Announcement::getByGroup( $groupId, $withArchive );
        if( !empty( $Events ) ) {
            foreach( $Events as $k=>$obj ) {
                $obj->getXml( $node );
            }
        }
    }

    /**
     * Добавляет категорию или обновляет категорию
     * @param string $name
     * @param int $id
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

}