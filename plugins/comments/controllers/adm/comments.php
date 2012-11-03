<?php

use Difra\Plugins, Difra\Param;

class AdmCommentsController extends Difra\Controller {

	public function dispatch() {
		$this->view->instance = 'adm';
	}

	public function lastAction() {

		$commentsRootNode = $this->root->appendChild( $this->xml->createElement( 'last-comments' ) );
		$commentsNode = $commentsRootNode->appendChild( $this->xml->createElement( 'comments' ) );
		\Difra\Plugins\Comments::getInstance()->getAllCommentsXML( $commentsNode );

	}

	public function deleteAjaxAction( Param\AnyString $module, Param\AnyInt $commentId ) {

		\Difra\Plugins\Comments\Comment::delete( $commentId->val(), $module->val() );
		$this->ajax->refresh();
	}
}

