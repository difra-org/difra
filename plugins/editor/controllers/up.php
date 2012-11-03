<?php

class UpController extends \Difra\Controller {

	/**
	 * Загрузка изображений во временное хранилище
	 */
	public function indexAction() {

		$this->view->rendered = true;
		if( !isset( $_GET['CKEditorFuncNum'] ) ) {
			die();
		}
		$funcnum = $_GET['CKEditorFuncNum'];
		if( !isset( $_FILES['upload'] ) or ( $_FILES['upload']['error'] != UPLOAD_ERR_OK ) ) {
			die( "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction($funcnum, '','"
			     . $this->locale->getXPath( 'editor/upload-error' ) . "');</script>" );
		}

		$img = \Difra\Libs\Images::getInstance()->convert( file_get_contents( $_FILES['upload']['tmp_name'] ) );
		if( !$img ) {
			die( "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction($funcnum,'','"
			     . $this->locale->getXPath( 'editor/upload-notimage' ) . "');</script>" );
		}
		try {
			$link = \Difra\Libs\Vault::add( $img );
			$link = "/up/tmp/$link";
		} catch( \Difra\Exception $ex ) {
			die( "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction($funcnum,'','"
			     . $ex->getMessage() . "');</script>" );
		}
		die( "<script type=\"text/javascript\">window.parent.CKEDITOR.tools.callFunction($funcnum,'$link');</script>" );
	}

	/**
	 * Отображение изображений из временного хранилища
	 * @param Difra\Param\AnyInt $id
	 */
	public function tmpAction( \Difra\Param\AnyInt $id ) {

		$data = \Difra\Libs\Vault::get( $id->val() );
		if( !$data ) {
			$this->view->httpError( 404 );
			return;
		}
		$this->view->rendered = true;
		header( 'Content-type: image/png' );
		echo $data;
	}
}