<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */
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

	public function alterAjaxAction( \Difra\Param\AnyString $name ) {

		try {
			/** @var \Difra\Unify\Item $class */
			$class = \Difra\Unify\Storage::getClass( $name->val() );
			$status = $class::getObjDbStatus();
			if( $status['status'] == 'alter' ) {
				\Difra\MySQL::getInstance()->query( $status['sql'] );
			}
		} catch( \Difra\Exception $ex ) {
			\Difra\Ajax::getInstance()->notify( $ex->getMessage() );
		}
		\Difra\Ajax::getInstance()->refresh();
	}
}