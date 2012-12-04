<?php

namespace Difra\Plugins\Announcements;

Class Category {

    private $id = null;
    private $textName = null;
    private $category = null;

    private $loaded = true;
    private $modified = false;


    public static function create( $id = null ) {

        $Categoty = new self;
        $Categoty->id = $id;
        return $Categoty;
    }

    /**
     * Чистит кэш
     * @static
     *
     */
    private static function cleanCache() {

        \Difra\Cache::getInstance()->remove( 'announcements_category' );
    }

    /**
     * Проверяет есть ли уже такая категория
     * @static
     * @param $name
     */
    public static function checkName( $category ) {

        $res = \Difra\Cache::getInstance()->get( 'announcements_category' );
        if( $res ) {
            foreach( $res as $k=>$data ) {
                if( $data['category'] == $category ) {
                    return true;
                }
            }
            return false;
        } else {
            $db = \Difra\MySQL::getInstance();
            $res = $db->fetchOne( "SELECT `id` FROM `announcements_category` WHERE `category`='" . $db->escape( $category ) . "'" );
            return !empty( $res ) ? true : false;
        }
    }

    /**
     * Устанавливает текстовое название категории
     * @param $name
     */
    public function setTextName( $name ) {

        $this->textName = trim( $name );
        $this->modified = true;
    }

    /**
     * Устанавливает категорию анонса
     * @param $category
     */
    public function setCategory( $category ) {

        $this->category = trim( $category );
        $this->modified = true;
    }

    /**
     * Сохраняет или апдейтит категорию
     */
    private function save() {

        $db = \Difra\MySQL::getInstance();

        if( !is_null( $this->id ) ) {
            // update
            $query = "UPDATE `announcements_category` SET `category`='" . $db->escape( $this->category ) .
                        "', `categoryText`='" . $db->escape( $this->textName ) . "' WHERE `id`='" . intval( $this->id ) . "'";

        } else {
            // insert
            $query = "INSERT INTO `announcements_category` SET `category`='" . $db->escape( $this->category ) .
                        "', `categoryText`='" . $db->escape( $this->textName ) . "'";
        }

        $db->query( $query );
    }

    /**
     * Возвращает в xml список всех категорий
     * @static
     * @param \DOMNode $node
     */
    public static function getList( $node ) {

        $Cache = \Difra\Cache::getInstance();

        $res = $Cache->get( 'announcements_category' );

        if( !$res ) {
            $db = \Difra\MySQL::getInstance();
            $query = "SELECT * FROM `announcements_category`";
            $res = $db->fetch( $query );
        } else {
            $node->setAttribute( 'cached', true );
        }

        if( !empty( $res ) ) {
            $saveToCache = null;
            foreach( $res as $k=>$data ) {
                $catNode = $node->appendChild( $node->ownerDocument->createElement( 'category' ) );
                $catNode->setAttribute( 'id', $data['id'] );
                $catNode->setAttribute( 'category', $data['category'] );
                $catNode->setAttribute( 'name', $data['categoryText'] );
                $saveToCache[$data['id']] = $data;
            }
            $Cache->put( 'announcements_category', $saveToCache, 10800 );
        }
    }

    /**
     * Удаляет категорию
     * @static
     * @param $id
     */
    public static function delete( $id ) {

        \Difra\MySQL::getInstance()->query( "DELETE FROM `announcements_category` WHERE `id`='" . intval( $id ) . "'" );
        self::cleanCache();
    }

    public function __destruct() {

        if( $this->modified && $this->loaded ) {
            $this->save();
            self::cleanCache();
        }
    }
}