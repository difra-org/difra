<?php

class RssController extends \Difra\Controller
{
    /**
     *    Пример контроллера для вывода rss из каталога
     */

    public function indexAction()
    {
        /*
        $Rss = \Difra\Plugins\Rss::make();

        if( empty( $Rss ) ) {
            return $this->view->httpError( 404 );
        }

        $this->view->rendered = true;

        if( !$Rss->checkCached() ) {

            $Config = \Difra\Config::getInstance();
            if( !$perPage = $Config->getValue( 'rss', 'size' ) ) {
                $perPage = 20;
            }

            $categoryList = \Difra\Plugins\Catalog\Category::getList();
            $list = \Difra\Plugins\Catalog\Item::getList( null, true, 1, $perPage, 1, true );

            if( ! empty( $list ) ) {
                foreach( $list as $k => $item ) {

                    $link = 'http://' . \Difra\Site::getInstance()->getMainhost() . $item->getFullLink();
                    $commentLink = '';

                    if( $Config->getValue( 'noiseSettings', 'commentOn' ) == 1 ) {
                        $commentLink = $link . '/comments/';
                    }

                    $itemArray = array(
                        'title' => $item->getName(),
                        'link' => $link,
                        'description' => $item->getDescription(),
                        'pubDate' => $item->getCreated(),
                        'guid' => $link,
                        'comments' => $commentLink
                    );
                    $Rss->setItem( $itemArray );
                }
            }
        }

        header( 'Content-type: text/xml' );
        echo $Rss->get();
        */
    }
}
