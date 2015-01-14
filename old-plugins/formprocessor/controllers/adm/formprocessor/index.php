<?php

use Difra\Plugins\FormProcessor;

class AdmFormprocessorIndexController extends \Difra\Controller {

    public function dispatch() {

        $this->view->instance = 'adm';
    }

    public function manageAction() {

        $rootNode = $this->root->appendChild( $this->xml->createElement( 'FP_manage' ) );
        $formsNode = $rootNode->appendChild( $this->xml->createElement( 'forms' ) );
        \Difra\Plugins\FormProcessor::getInstance()->getListXML( $formsNode );
    }

    public function addAction() {

        $rootNode = $this->root->appendChild( $this->xml->createElement( 'FP_create' ) );
    }

    public function saveformAjaxAction( \Difra\Param\AjaxString $name, \Difra\Param\AjaxString $uri,
                                        \Difra\Param\AjaxString $notify, \Difra\Param\AjaxString $button, \Difra\Param\AjaxHTML $description = null,
                                        \Difra\Param\AjaxData $fieldType = null, \Difra\Param\AjaxData $fieldName = null,
                                        \Difra\Param\AjaxData $fieldDescription = null, \Difra\Param\AjaxData $fieldMandatory = null,
                                        \Difra\Param\AjaxData $selectVariants = null,
                                        \Difra\Param\AjaxInt $formId = null, \Difra\Param\AjaxString $originalUri = null ) {

        if( is_null( $fieldType ) || is_null( $fieldName ) ) {
            return $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'formProcessor/adm/create/noFieldsNotify' ) );
        }

        $FP = \Difra\Plugins\FormProcessor::getInstance();
        $uri = $uri->val();
        $originalUri = !is_null( $originalUri ) ? $originalUri->val() : null;

        if( is_null( $formId ) && $uri != $originalUri ) {
            if( $FP->checkDupUri( $uri ) ) {
                return $this->ajax->invalid( 'uri', \Difra\Locales::getInstance()->getXPath( 'formProcessor/adm/create/duplicateUri' ) );
            }
        }

        $errorField = $FP->checkEmptyNameFields( $fieldType, $fieldName );
        if( $errorField !== true ) {
            return $this->ajax->notify( $errorField );
        }

        $mainFieldsArray = array( 'title' => $name->val(), 'uri' => $uri, 'answer' => $notify->val(),
                                    'submit' => $button->val(), 'description' => $description );

        $fieldMandatory = !is_null( $fieldMandatory ) ? $fieldMandatory->val() : null;
        $selectVariants = !is_null( $selectVariants ) ? $selectVariants->val() : null;

        $formFieldsArray = array( 'names' => $fieldName->val(), 'mandatory' => $fieldMandatory, 'types' => $fieldType->val(),
                                    'descriptions' => $fieldDescription->val(), 'variants' => $selectVariants );

        if( !is_null( $formId ) ) {

            if( !$FP->updateForm( $formId->val(), $mainFieldsArray, $formFieldsArray ) ) {
                return $this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'formProcessor/adm/edit/idError' ) );
            }
            \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'formProcessor/adm/edit/formUpdated' ) );

        } else {
            $FP->createForm( $mainFieldsArray, $formFieldsArray );
            \Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'formProcessor/adm/create/formAdded' ) );
        }

        $this->ajax->redirect( '/adm/formprocessor/manage' );
    }

    public function changestatusAjaxAction( \Difra\Param\AnyInt $id ) {

        \Difra\Plugins\FormProcessor::getInstance()->changeStatus( $id->val() );
        $this->ajax->refresh();
    }

    public function deleteAjaxAction( \Difra\Param\AnyInt $id ) {

        \Difra\Plugins\FormProcessor::getInstance()->deleteForm( $id->val() );
        $this->ajax->refresh();
    }

    public function editAction( \Difra\Param\AnyInt $id ) {

        $rootNode = $this->root->appendChild( $this->xml->createElement( 'FP_editform' ) );
        $formNode = $rootNode->appendChild( $this->xml->createElement( 'form' ) );
        if( !\Difra\Plugins\FormProcessor::getInstance()->getFormXML( $formNode, $id->val() ) ) {
            $this->view->httpError( 404 );
        }
    }

}