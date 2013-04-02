<?php

use Difra\Plugins\News;

class AdmNewsController extends \Difra\Controller {

    public function dispatch() {

        $this->view->instance = 'adm';
    }

    public function indexAction() {

        $node = $this->root->appendChild( $this->xml->createElement( 'news-view' ) );
        \Difra\Plugins\News::getInstance()->getListXML( $node, null, true );
    }

    public function addAction() {
        $node = $this->root->appendChild( $this->xml->createElement( 'publication-add' ) );
    }

    public function saveAjaxAction( \Difra\Param\AjaxString $title, \Difra\Param\AjaxString $pubDate,
                                    \Difra\Param\AjaxString $viewDate, \Difra\Param\AjaxCheckbox $visible,
                                    \Difra\Param\AjaxCheckbox $important, \Difra\Param\AjaxHTML $body,
                                    \Difra\Param\AjaxString $stopDate = null, \Difra\Param\AjaxHTML $announcement = null,
                                    \Difra\Param\AjaxString $sourceName = null, \Difra\Param\AjaxString $sourceURL = null,
                                    \Difra\Param\AjaxInt $id = null ) {

        $data = array( 'title' => $title->val(), 'pubDate' => $pubDate->val(), 'viewDate' => $viewDate->val(),
                        'visible' => $visible->val(), 'important' => $important->val(), 'body' => $body );

        $data['stopDate'] = !is_null( $stopDate ) ? $stopDate->val() : null;
        $data['announcement'] = !is_null( $announcement ) ? $announcement : null;
        $data['sourceName'] = !is_null( $sourceName ) ? $sourceName->val() : null;
        $data['sourceURL'] = !is_null( $sourceURL ) ? $sourceURL->val() : null;
        $data['id'] = !is_null( $id ) ? $id->val() : null;

        \Difra\Plugins\News::getInstance()->addNews( $data );

        if( !is_null( $id ) ) {
            $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'news/adm/updated' ) );
        } else {
            $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'news/adm/add/added' ) );
        }
        $this->ajax->redirect( '/adm/news/' );
    }

    public function statusAjaxAction( \Difra\Param\AnyInt $newsId, \Difra\Param\AnyString $status ) {

        \Difra\Plugins\News::getInstance()->changeStatus( $newsId->val(), $status->val() );
        $this->ajax->refresh();
    }

    public function importantAjaxAction( \Difra\Param\AnyInt $newsId, \Difra\Param\AnyString $status ) {

        \Difra\Plugins\News::getInstance()->changeImportant( $newsId->val(), $status->val() );
        $this->ajax->refresh();
    }

    public function deleteAjaxAction( \Difra\Param\AnyInt $newsId ) {

        \Difra\Plugins\News\Publication::delete( $newsId->val() );
        $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'news/adm/deleted' ) );
        $this->ajax->refresh();
    }

    public function editAction( \Difra\Param\AnyInt $newsId ) {

        $node = $this->root->appendChild( $this->xml->createElement( 'publication-edit' ) );
        $Pub = \Difra\Plugins\News\Publication::getById( $newsId->val() );
        if( $Pub ) {
            $Pub->getXML( $node );
        } else {
            $this->view->httpError( 404 );
        }
    }

    public function settingsAction() {

        $node = $this->root->appendChild( $this->xml->createElement( 'news-settings' ) );
        $settings = \Difra\Config::getInstance()->get( 'news_settings' );
        if( !empty( $settings ) ) {
            foreach( $settings as $k=>$value ) {
                $node->setAttribute( $k, $value );
            }
        }

    }

    public function savesettingsAjaxAction( \Difra\Param\AjaxInt $perPage ) {

        $settingsArray = array( 'perPage' => intval( $perPage->val() ) );
        \Difra\Plugins\News::getInstance()->saveSettings( $settingsArray );
        $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'news/adm/settings/saved' ) );
        $this->ajax->refresh();
    }

}