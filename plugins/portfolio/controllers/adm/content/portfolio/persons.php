<?php

class AdmContentPortfolioPersonsController extends \Difra\Plugins\Widgets\DirectoryController {

	const directory = 'PortfolioPersons';

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function action( $value ) {

		$escapedValue = htmlspecialchars( $value );
		\Difra\Ajax::getInstance()->exec(
			<<<SCRIPT
			if( $( '.widgets-directory.last' ).closest( '.add-person' ).length ) {
	$( '.widgets-directory.last' ).before(
		'<tr><td>$escapedValue</td><td class=".add-person"><a href="/adm/content/portfolio/persons" class="action add ajaxer widgets-directory"></a></td></tr>'
	);
}
SCRIPT
		);
	}
}