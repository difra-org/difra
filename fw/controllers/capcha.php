<?php

class CapchaController extends Difra\Controller {

	public function indexAction() {

		$Capcha = Difra\Libs\Capcha::getInstance();
		$Capcha->setSize( 105, 36 );
		//$Capcha->setKeyLength( 4 );
		header( 'Content-type: image/png' );
		header( "Expires: Sat, 26 Jul 1997 05:00:00 GMT" ); // Some time in the past
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );
		header( "Cache-Control: no-store, no-cache, must-revalidate" );
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );
		echo $Capcha->viewCapcha();
		\Difra\View::$rendered = true;
	}
}