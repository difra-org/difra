<?php

class AdmSettingsRssIndexController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction() {

		$setNode = $this->root->appendChild( $this->xml->createElement( 'rss_settings' ) );
		Difra\Plugins\Rss::getSettingsXML( $setNode );


	}

	public function savesettingsAjaxAction(
		\Difra\Param\AjaxCheckbox $onLine,
		\Difra\Param\AjaxString $title,
		\Difra\Param\AjaxString $link,
		\Difra\Param\AjaxInt $ttl,
		\Difra\Param\AjaxInt $size,
		\Difra\Param\AjaxCheckbox $image,
		\Difra\Param\AjaxCheckbox $cache,
		\Difra\Param\AjaxString $desc = null,
		\Difra\Param\AjaxString $copyright = null
	) {

		$settingsArray = array( 'onLine' => $onLine->val(), 'title' => $title->val(), 'link' => $link->val(),
					'ttl' => $ttl->val(), 'size' => $size->val(), 'image' => $image->val(), 'cache' => $cache->val() );

		if( !is_null( $desc ) ) {
			$settingsArray['description'] = $desc->val();
		}
		if( !is_null( $copyright ) ) {
			$settingsArray['copyright'] = $copyright->val();
		}

		Difra\Plugins\Rss::saveSettings( $settingsArray );
		$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'rss/adm/saved' ) );

	}

}