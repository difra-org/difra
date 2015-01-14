<?php

class AdmContentPortfolioPersonsController extends \Difra\Plugins\Widgets\DirectoryController {

	const directory = 'PortfolioPersons';

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function action( $value ) {

		$escapedValue = addslashes( htmlspecialchars( $value ) );
		\Difra\Ajaxer::getInstance()->exec(
			<<<SCRIPT
			var person = $( '.widgets-directory.last' );
			if( person.length ) {
				var id = person.closest( 'tr' ).find( '.portfolio-role' ).attr( 'ts' );
				person.before(
					'<div class="portfolio-person">' +
					  '$escapedValue' +
					  '<input type="hidden" name="roles[' + id + '][]" value="$escapedValue">' +
					  '<a href="#" class="action delete" onclick="$(this).parent().remove();"></a>' +
	        			'</div>'
	        		);
	        	}
SCRIPT
		);
	}
}