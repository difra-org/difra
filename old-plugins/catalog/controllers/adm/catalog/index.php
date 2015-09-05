<?php

use Difra\Plugins\Catalog, Difra\Param;

class AdmCatalogIndexController extends Difra\Controller
{
    public function dispatch()
    {

        \Difra\View::$instance = 'adm';
    }
    /*
    public function indexAction( Param\AnyInt $category = null ) {

                $categoriesNode = $this->root->appendChild( $this->xml->createElement( 'categorytree' ) );
                Catalog\Categories::getInstance()->getXML( $categoriesNode );

        if( $category ) {
            $this->root->setAttribute( 'category', $category->val() );
            $goodiesNode = $this->root->appendChild( $this->xml->createElement( 'goodiesList' ) );
            Catalog::getInstance()->getGoodiesXML( $goodiesNode, $category->val(), false );
        }
    }

    public function addAction( Param\AnyInt $category = null ) {

        if( !is_null( $category ) ) {
            $category = $category->val();
        } elseif( isset( $_POST['goodie_add_category'] ) ) {
            $category = $_POST['goodie_add_category'];
        } else {
            $this->view->redirect( '/adm/catalog' );
        }

        $addNode = $this->root->appendChild( $this->xml->createElement( 'goodieAdd' ) );
        $addNode->setAttribute( 'action', 'add' );
        $form = \Difra\Form::getInstance( 'goodie_add' );
        $form->putFormData( array( 'category' => $category ) );
        if( $form->checkForm() ) {
            $data = $form->getFormData();
            $data['ext'] = !empty( $_POST['ext'] ) ? $_POST['ext'] : array();
            $id = Catalog::getInstance()->addGoodie( $data );
            if( isset( $_FILES['goodie_add_image'] ) ) {
                Catalog\Images::add( $id, $_FILES['goodie_add_image'], true );
            }
            $this->view->redirect( '/adm/catalog/' . $category );
            return;
        }
        $form->getFormXML( $addNode );
        $extNode = $addNode->appendChild( $this->xml->createElement( 'ext' ) );
        Catalog\Ext::getInstance()->getListXML( $extNode, false );
    }

    public function delAction( Param\AnyInt $category, Param\AnyInt $id ) {

        $delNode = $this->root->appendChild( $this->xml->createElement( 'goodieDel' ) );
        $delNode->setAttribute( 'category', $category->val() );
        $delNode->setAttribute( 'id', $id->val() );
        Catalog::getInstance()->getGoodyXML( $delNode, $id->val() );

    }

    public function realdelAction( Param\AnyInt $category, Param\AnyInt $id ) {

        \Difra\Plugins\Catalog::getInstance()->deleteGoodie( $id->val() );
        return $this->view->redirect( "/adm/catalog/$category" );
    }

    public function editAction( Param\AnyInt $category = null, Param\AnyInt $id = null ) {

        if( !is_null( $category ) and !is_null( $id ) ) {
            $category = $category->val();
            $id = $id->val();
        } elseif( isset( $_POST['goodie_edit_category'] ) and isset( $_POST['goodie_edit_id'] ) ) {
            $category = $_POST['goodie_edit_category'];
            $id = $_POST['goodie_edit_id'];
        } else {
            $this->view->redirect( '/adm/catalog' );
        }

        $editNode = $this->root->appendChild( $this->xml->createElement( 'goodieEdit' ) );
        $editNode->setAttribute( 'id', $id );
        $editNode->setAttribute( 'action', 'edit' );
        $form = \Difra\Form::getInstance( 'goodie_edit' );
        $form->putFormData( Catalog::getInstance()->getGoodyRaw( $id ) );
        if( $form->checkForm() ) {
            $data = $form->getFormData();
            $data['ext'] = !empty( $_POST['ext'] ) ? $_POST['ext'] : array();
            $data['coef'] = !empty( $_POST['coef'] ) ? $_POST['coef'] : array();
            Catalog::getInstance()->updateGoodie( $id, $data );
            $this->view->redirect( '/adm/catalog/' . $category );
        }
        $form->getFormXML( $editNode );
        $extNode = $editNode->appendChild( $this->xml->createElement( 'ext' ) );
        Catalog\Ext::getInstance()->getListXML( $extNode, false );
        $dataNode = $editNode->appendChild( $this->xml->createElement( 'data' ) );
        Catalog::getInstance()->getGoodyXML( $dataNode, $id );
    }
    */
}
