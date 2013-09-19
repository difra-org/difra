<?php

class AdmStatusUnifyController extends Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function createAjaxAction( \Difra\Param\AnyString $name ) {

		try {
			/** @var \Difra\Unify\Item $class */
			$class = \Difra\Unify\Storage::getClass( $name->val() );
			$class::createDb();
		} catch( \Difra\Exception $ex ) {
			\Difra\Ajax::getInstance()->notify( $ex->getMessage() );
		}
		\Difra\Ajax::getInstance()->refresh();
	}
}