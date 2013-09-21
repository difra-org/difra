<?php

class AdmContentPortfolioRolesController extends \Difra\Plugins\Widgets\DirectoryController {

	const directory = 'PortfolioRoles';

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}
}