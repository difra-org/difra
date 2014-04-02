<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */
class AdmIndexController extends Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		\Difra\View::redirect( '/adm/status' );
	}
}
