<?php

class AdmGalleryAlbumController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	public function indexAction(\Difra\Param\AnyInt $id) {

		/** @var \DOMElement $albumNode */
		$albumNode = $this->root->appendChild($this->xml->createElement('GalleryAlbumView'));

		$album = \Difra\Plugins\Gallery\Album::get($id->val());

		if(!$album->load()) {
			throw new \Difra\View\Exception(404);
		}

		$album->getXML($albumNode);
		$albumNode->setAttribute('id', $id);
		$sizesNode = $albumNode->appendChild($this->xml->createElement('sizes'));
		$album->getSizesXML($sizesNode);
	}

	public function addAjaxAction(\Difra\Param\AjaxInt $album, \Difra\Param\AjaxFiles $image) {

		\Difra\Plugins\Gallery::getInstance()->imageAdd($album, $image);
		$this->ajax->refresh();
	}

	public function upAjaxAction(\Difra\Param\AnyInt $albumId, \Difra\Param\AnyInt $imageId) {

		\Difra\Plugins\Gallery::getInstance()->imageUp($albumId, $imageId);
		$this->ajax->refresh();
	}

	public function downAjaxAction(\Difra\Param\AnyInt $albumId, \Difra\Param\AnyInt $imageId) {

		\Difra\Plugins\Gallery::getInstance()->imageDown($albumId, $imageId);
		$this->ajax->refresh();
	}

	public function deleteAjaxAction(\Difra\Param\AnyInt $albumId, \Difra\Param\AnyInt $imageId) {

		\Difra\Plugins\Gallery::getInstance()->imageDelete($albumId, $imageId);
		$this->ajax->refresh();
	}
}