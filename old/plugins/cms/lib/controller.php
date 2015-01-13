<?php

/**
 * В действии pre-action определяется, зашел ли пользователь на страницу CMS (см. plugin.php).
 * Если зашел, Action настраивается так, чтобы вызывался этот контроллер.
 */

namespace Difra\Plugins\CMS;

/**
 * Class Controller
 *
 * @package Difra\Plugins\CMS
 */
class Controller extends \Difra\Controller {

	/**
	 * @param \Difra\Param\AnyInt $id
	 */
	public function pageAction( \Difra\Param\AnyInt $id ) {

		/** @var $pageNode \DOMElement */
		$pageNode = $this->root->appendChild( $this->xml->createElement( 'page' ) );
		$page = Page::get( $id->val() );
		$page->getXML( $pageNode );
		$this->root->setAttribute( 'pageTitle', $page->getTitle() );
	}
}