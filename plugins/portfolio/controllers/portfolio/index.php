<?php

use Difra\Param, Difra\Plugins\Portfolio;

class PortfolioIndexController extends \Difra\Controller {

	public function indexAction( Param\AnyString $portfolioLink = null ) {

		if( !is_null( $portfolioLink ) ) {
			$this->_viewWork( $portfolioLink->val() );
			return;
		}

		$mainXml = $this->root->appendChild( $this->xml->createElement( 'PortfolioView' ) );
		$this->root->setAttribute( 'pageTitle', \Difra\Locales::getInstance()->getXPath( 'portfolio/adm/portfolio' ) );


		$search = new \Difra\Unify\Search( 'PortfolioEntry' );
		$search->setOrder( 'release', 'release' );
		$workList = $search->getList();
		$idArray = array();
		$newYear = 0;
		foreach( $workList as $work ) {
			$workNode = $mainXml->appendChild( $this->xml->createElement( 'PortfolioEntry' ) );
			$work->getXML( $workNode );
			if( !is_null( $work->release ) ) {
				$xRelease = explode( '-', $work->release );
				if( $newYear != $xRelease[0] ) {
					$workNode->setAttribute( 'newYear', $xRelease[0] );
					$newYear = $xRelease[0];
				}
			}
			$idArray[] = $work->id;
		}

		if( !empty( $idArray ) ) {
			\Difra\Plugins\Portfolio::getMainImagesXML( $idArray, $mainXml );
		}

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
		$this->root->setAttribute( 'pageTitle', $list[0]->name );
	}


}