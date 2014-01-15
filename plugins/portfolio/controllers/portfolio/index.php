<?php

use Difra\Param, Difra\Plugins\Portfolio;

class PortfolioIndexController extends \Difra\Controller {

	public function indexAction( Param\AnyString $portfolioLink = null ) {

		if( !is_null( $portfolioLink ) ) {
			$this->_viewWork( $portfolioLink->val() );
			return;
		}

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'PortfolioView' ) );

	}

	private function _viewWork( $link ) {

		$entry = new \Difra\Unify\Search( 'PortfolioEntry' );
		$entry->addCondition( 'uri', $link );
		$list = $entry->getList();
		if( empty( $list ) ) {
			throw new \Difra\View\Exception( 404 );
		}

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'PortfolioWork' ) );
		$list[0]->getXML( $mainXml );
		\Difra\Plugins\Portfolio::getWorkImagesXML( $list[0]->id, $mainXml );
	}


}