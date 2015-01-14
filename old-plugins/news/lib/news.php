<?php

namespace Difra\Plugins;

class News {

    /**
     * @static
     * @return FormProcessor
     */
    static public function getInstance() {

        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * Создаёт новость
     * @param array $data
     */
    public function addNews( $data ) {

        if( !empty( $data ) ) {
            $Pub = \Difra\Plugins\News\Publication::create( $data['id'] );
            $Pub->setTitle( $data['title'] );
            $Pub->setPubDate( $data['pubDate'] );
            $Pub->setViewDate( $data['viewDate'] );
            $Pub->setVisible( $data['visible'] );
            $Pub->setImportant( $data['important'] );
            $Pub->setStopDate( $data['stopDate'] );
            $Pub->setSourceName( $data['sourceName'] );
            $Pub->setSourceURL( $data['sourceURL'] );
            $Pub->setBody( $data['body'] );
            $Pub->setAnnouncement( $data['announcement'] );
            return true;
        }
        return false;
    }

    /**
     * Возвращает в xml список новостей
     * @param \DOMNode $node
     * @param bool $onlyActive
     */
    public function getListXML( $node, $page = null, $withoutText = false, $onlyActive = false ) {

        $newsList = \Difra\Plugins\News\Publication::getList( $page, $onlyActive );

        if( !empty( $newsList ) ) {

            foreach( $newsList as $id=>$object ) {
                $object->getXML( $node, $withoutText );
            }

            if( !is_null( $page ) ) {
                $totalPages = $this->getTotalCount( $onlyActive );
                $perPage = \Difra\Config::getInstance()->getValue( 'news_settings', 'perPage' );
                $pages = floor( ( $totalPages - 1 ) / $perPage ) + 1;
                $node->setAttribute( 'pages', $pages );
                $node->setAttribute( 'current', $page );
                $node->setAttribute( 'link', '/news' );
            }

        } else {
            $node->appendChild( $node->ownerDocument->createElement( 'empty' ) );
        }
    }

    /**
     * Возвращает XML новости по её uri
     * @param string $link
     * @param \DOMNode $node
     */
    public function getByLinkXML( $link, $node ) {

        $Pub = \Difra\Plugins\News\Publication::getByLink( rawurldecode( $link ) );
        if( $Pub ) {

            $Pub->getXML( $node );
            return true;
        }

        return false;
    }

    /**
     * Возвращает количество новостей
     * @param bool $onlyActive
     */
    public function getTotalCount( $onlyActive = false ) {

        $where = '';
        $totalPages = 0;
        $db = \Difra\MySQL::getInstance();
        if( $onlyActive ) {
            $where = " WHERE `visible`=1 AND `viewDate`<=NOW() AND (( NOT(`stopDate`='0000-00-00 00:00:00') AND `stopDate`>=NOW() ) "
                    . "OR `stopDate`='0000-00-00 00:00:00') ";
        }
        $query = "SELECT COUNT( `id` ) AS `total` FROM `news` " . $where;
        $res = $db->fetchRow( $query );
        if( ! empty( $res ) ) {
            $totalPages = $res['total'];
        }
        return $totalPages;
    }

    /**
     * Включает и выключает показ новости
     * @param int $id
     * @param string $status
     */
    public function changeStatus( $id, $status ) {

        $status = $status == 'on' ? 1 : 0;
        $Pub = \Difra\Plugins\News\Publication::getById( $id );
        $Pub->setVisible( $status );
    }

    /**
     * Делает публикацию важной или нет
     * @param $id
     * @param $status
     */
    public function changeImportant( $id, $status ) {

        $status = $status == 'on' ? 1 : 0;
        $Pub = \Difra\Plugins\News\Publication::getById( $id );
        $Pub->setImportant( $status );
    }

    /**
     * Сохраняет настройки новостей
     * @param array $settingsArray
     */
    public function saveSettings( $settingsArray ) {
        \Difra\Config::getInstance()->set( 'news_settings', $settingsArray );
    }

    /**
     * Возвращает массив с новостями для карты сайта
     */
    public function getSiteMapArray() {

        $db = \Difra\MySQL::getInstance();

        $where = " WHERE `visible`=1 AND `viewDate`<=NOW() AND (( NOT(`stopDate`='0000-00-00 00:00:00') AND `stopDate`>=NOW() ) "
                . "OR `stopDate`='0000-00-00 00:00:00') ";
        $query = "SELECT `id`, `link`, `modified` FROM `news` " . $where;
        $res = $db->fetch( $query );
        if( !empty( $res ) ) {
            $returnArray = null;
            $mainLink = 'http://' . \Difra\Site::getInstance()->getHostname() . '/news/';
            foreach( $res as $k=>$data ) {

                $returnArray[] = array( 'loc' => $mainLink . $data['id'] . '-' . $data['link'],
                                        'lastmod' => date( 'Y-m-d', strtotime( $data['modified'] ) )
                                        );

            }
            return $returnArray;
        }
        return false;
    }

}