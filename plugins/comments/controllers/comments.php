<?php

use Difra\Plugins\Comments, Difra\Param;

class CommentsController extends Difra\Controller {

	public function addAjaxActionAuth( \Difra\Param\AjaxString $module, \Difra\Param\AjaxInt $moduleId,
                                       \Difra\Param\AjaxInt $replyId, \Difra\Param\AjaxString $text ) {

        $commentArray = array( 'module' => $module->val(), 'moduleId' => intval( $moduleId->val() ),
                                'replyId' => intval( $replyId->val() ), 'text' => $text->val() );

		Comments::getInstance()->add( $commentArray );
        //$this->ajax->notify( $moduleId->val() );

        $this->ajax->refresh();
	}

	public function deletenotifyAjaxActionAuth( Param\AjaxString $id = null, Param\AjaxString $module = null ) {

		$this->ajax->display( Difra\Locales::getInstance()->getXPath( 'comments/notifies/delete' ) .
				      '<br/><br/><div href="#" onclick="comments.delete( ' . intval( $id->val() ) . ', \'' .
				      $module->val(). '\' );" class="button">Да</div>'
				      . '<a href="#" style="display: inline-block; margin-left:15px;" class="button" onclick="ajaxer.close(this)">Нет</a>' );
	}

	public function deleteAjaxActionAuth( Param\AjaxString $id = null, Param\AjaxString $module = null ) {

		if( is_null( $id ) || is_null( $module ) ) {
			$this->ajax->setResponse( 'success', false );
			die();
		}

		if( \Difra\Plugins\Comments\Comment::checkDeleteRights( $id->val(), $module->val() ) )  {

			\Difra\Plugins\Comments\Comment::delete( $id->val(), $module->val() );
			$this->ajax->setResponse( 'success', true );
			return;
		}

		$this->ajax->setResponse( 'success', false );
	}

}
