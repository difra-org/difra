<?php

class AdmContentPortfolioRolesController extends \Difra\Plugins\Widgets\DirectoryController
{
    const directory = 'PortfolioRoles';

    public function dispatch()
    {

        \Difra\View::$instance = 'adm';
    }

    public function action($value)
    {

        $escapedValue = addslashes(htmlspecialchars($value));
        $ts = microtime(true);
        \Difra\Ajaxer::getInstance()->exec(
            <<<SCRIPT
			if( $( '.widgets-directory.last' ).closest( '#add-role' ).length ) {
	$( '#add-role' ).before(
		'<tr>' +
		 '<td>' +
		  '<a href="#" class="action delete" onclick="$(this).parent().parent().remove()"></a> &nbsp; ' +
		  '$escapedValue' +
		  '<input type="hidden" name="roles[$ts][role]" value="$escapedValue" class="portfolio-role" ts="$ts">' +
	         '</td>' +
		 '<td class="add-person"><a href="/adm/content/portfolio/persons" class="action add ajaxer widgets-directory"></a></td>' +
		'</tr>'
	);
}
SCRIPT
        );
    }
}