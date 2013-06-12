<?php

use Difra\Plugins, Difra\Param;

class AdmContentTagsController extends Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$tagsNode = $this->root->appendChild( $this->xml->createElement( 'adm_tags' ) );
		$Tags = Plugins\Tags::getInstance();
		$Tags->getAllTagsXML( $tagsNode );

		$aliasesNode = $this->root->appendChild( $this->xml->createElement( 'aliases' ) );
		$Tags->getAliasesXML( $aliasesNode );
	}

	public function saveAjaxAction( \Difra\Param\AjaxInt $tagId, \Difra\Param\AjaxString $module,
					\Difra\Param\AjaxString $tagName ) {

		if( \Difra\Plugins\Tags::getInstance()->saveTag( $module->val(), $tagId->val(), $tagName->val() ) ) {

			\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'tags/adm/tagSaved' ) );
			$this->ajax->reload();
		} else {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'tags/adm/notSaved' ) );
		}
	}

	public function deleteAjaxAction( \Difra\Param\AjaxInt $tagId, \Difra\Param\AjaxString $module ) {

		if( \Difra\Plugins\Tags::getInstance()->deleteTag( $module->val(), $tagId->val() ) ) {

			\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'tags/adm/tagDeleted' ) );
			$this->ajax->reload();
		} else {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'tags/adm/notDeleted' ) );
		}
	}

	public function editAjaxAction( \Difra\Param\AnyString $module, \Difra\Param\AnyInt $tagId ) {

		$tagData = \Difra\Plugins\Tags::getInstance()->getTag( $module->val(), $tagId->val() );
		if( !empty( $tagData ) ) {

			$mainNode = $this->root->appendChild( $this->xml->createElement( 'tagsEditForm' ) );
			$mainNode->setAttribute( 'id', $tagData['id'] );
			$mainNode->setAttribute( 'module', $module->val() );
			$mainNode->setAttribute( 'tag', $tagData['tag'] );

			$html = $this->view->render( $this->xml, 'forms', true );
			$this->ajax->display( $html );
		}
	}

	public function deletealiasAjaxAction( \Difra\Param\AnyInt $aliasId ) {

		\Difra\Plugins\Tags::getInstance()->deleteAlias( $aliasId->val() );

		\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'tags/adm/aliasDeleted' ) );
		$this->ajax->reload();
	}

	public function createaliaseAjaxAction( \Difra\Param\AjaxInt $tagId, \Difra\Param\AjaxString $module, \Difra\Param\AjaxString $aliase ) {

		if( \Difra\Plugins\Tags::getInstance()->createAliase( $module->val(), $tagId->val(), $aliase->val() ) ) {

			\Difra\Libs\Cookies::getInstance()->notify( \Difra\Locales::getInstance()->getXPath( 'tags/adm/aliasCreated' ) );
			$this->ajax->reload();
		} else {
			$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'tags/adm/aliasCreateError' ) );
		}
	}
}