<?php

class GalleryController extends \Difra\Controller
{
    public function indexAction(\Difra\Param\AnyInt $id = null, \Difra\Param\NamedInt $page = null)
    {

        if (!is_null($id)) {
            /** @var \DOMElement $albumNode */

            $albumNode = $this->root->appendChild($this->xml->createElement('GalleryAlbum'));

            $album = \Difra\Plugins\Gallery\Album::get($id->val());

            if (!$album->load()) {
                throw new \Difra\View\HttpError(404);
            }

            $album->getXML($albumNode);
            $albumNode->setAttribute('id', $id);
            $sizesNode = $albumNode->appendChild($this->xml->createElement('sizes'));
            $album->getSizesXML($sizesNode);
            $Locale = \Difra\Locales::getInstance();
            $pageTitle =
                $Locale->getXPath('gallery/title-album') . $Locale->getXPath('gallery/arrow') . $album->getName();
            $this->root->setAttribute('pageTitle', $pageTitle);
        } else {

            $perpage = \Difra\Config::getInstance()->getValue('gallery', 'perpage');
            $listNode = $this->root->appendChild($this->xml->createElement('GalleryList'));
            \Difra\Plugins\Gallery::getInstance()->getAlbumsListXML($listNode, true, $page ? $page->val() : 1,
                $perpage ? $perpage : 20);
            $this->root->setAttribute('pageTitle', \Difra\Locales::getInstance()->getXPath('gallery/title'));
        }
    }
}
