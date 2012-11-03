<?php

use Difra\Plugins, Difra\Param;

class AdmTagsController extends Difra\Controller {

	public function dispatch() {

		$this->view->instance = 'adm';
	}

	public function indexAction() {

		$Tags     = \Difra\Plugins\Tags::getInstance();
		$tagsNode = $this->root->appendChild( $this->xml->createElement( 'adm_tags' ) );
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

			$Locale = \Difra\Locales::getInstance();

			$this->ajax->display( '<form class="ajaxer" action="/adm/tags/save" id="tagEditForm">
							<h2>' . $Locale->getXPath( 'tags/adm/editTagTitle' ) . '</h2>

							<input type="hidden" name="tagId" value="' . $tagData['id'] . '"/>
							<input type="hidden" name="module" value="' . $module->val() . '"/>

							<label for="tagName">' . $Locale->getXPath( 'tags/adm/tagName' ) . '</label>
							<input type="text" value="' . $tagData['tag'] . '" name="tagName"/>

							<br/>
							<input type="submit" value="' . $Locale->getXPath( 'adm/save' ) . '"/>
							<input type="button" value="' . $Locale->getXPath( 'tags/adm/createAliase' ) . '"
								onclick="createAliaseForm();"/>
						</form>
						<form class="ajaxer" action="/adm/tags/delete" id="tagDeleteForm">
							<input type="hidden" name="tagId" value="' . $tagData['id'] . '"/>
							<input type="hidden" name="module" value="' . $module->val() . '"/>
							<input type="submit" value="' . $Locale->getXPath( 'adm/actions/delete' ) . '"/>
						</form>
						<form class="ajaxer" action="/adm/tags/createaliase/" id="createAliaseForm">
						<h2>Создание альяса для тега</h2>
							<input type="hidden" name="tagId" value="' . $tagData['id'] . '"/>
							<input type="hidden" name="module" value="' . $module->val() . '"/>
							<label for="aliase">Альяс для тега</label>
							<input type="text" name="aliase"/>
							<br/>
							<input type="submit" value="' . $Locale->getXPath( 'adm/save' ) . '"/>
						</form>
							<input type="button" value="' . $Locale->getXPath( 'adm/cancel' ) . '"
								onclick="javascript: ajaxer.close(this);"/>' );
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