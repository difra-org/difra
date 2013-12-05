<?php

use Difra\Plugins, Difra\Param;

class AdmUsersFilterController extends Difra\Controller {

	public function dispatch() {
		\Difra\View::$instance = 'adm';
	}

	public function saveAjaxAction( Param\AjaxCheckbox $active, Param\AjaxCheckbox $ban,
					Param\AjaxCheckbox $moderator, Param\AjaxCheckbox $noLogin ) {

		$paramsArray = array( 'active' => $active->val(), 'ban' => $ban->val(),
				      'moderator' => $moderator->val(), 'noLogin' => $noLogin->val() );

		Plugins\Users\Filter::setFilter( $paramsArray );
		$this->ajax->refresh();
	}

	public function sortAjaxAction( Param\AjaxString $sort, Param\AjaxString $order ) {

		Plugins\Users\Filter::setSortOrder( $sort->val(), $order->val() );
		$this->ajax->refresh();
	}

}
