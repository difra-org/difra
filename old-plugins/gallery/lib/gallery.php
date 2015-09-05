<?php

namespace Difra\Plugins;

class Gallery
{
    /**
     * Синглтон
     * @return Gallery
     */
    static public function getInstance()
    {

        static $self = null;
        return $self ? $self : $self = new self;
    }

    /**
     * Альбомы
     */

    /**
     * Возвращает список альбомов в XML
     * @param \DOMNode $node
     * @param bool|null $visible
     * @param int $page
     * @param int $perpage
     */
    public function getAlbumsListXML($node, $visible = null, $page = null, $perpage = null)
    {

        if (!$list = \Difra\Plugins\Gallery\Album::getList($visible, $page, $perpage)) {
            return;
        }
        foreach ($list as $album) {
            $albumNode = $node->appendChild($node->ownerDocument->createElement('album'));
            $album->getXML($albumNode);
            $album->getSizesXML($albumNode);
        }
    }

    /**
     * Возвращает данные альбома в XML
     * @param \DOMNode $node
     * @param int $id
     * @return bool
     */
    public function getAlbumXML($node, $id)
    {

        $album = \Difra\Plugins\Gallery\Album::get($id);
        if (!$album->load()) {
            return false;
        }
        $album->getXML($node);
        return true;
    }

    public function albumAdd($name, $description, $visible = true)
    {

        $album = \Difra\Plugins\Gallery\Album::create();
        $album->setName($name);
        $album->setDescription($description);
        $album->setVisible($visible);
    }

    public function albumUpdate($id, $name, $description, $visible = true)
    {

        $album = \Difra\Plugins\Gallery\Album::get($id);
        if (!$album->load()) {
            return false;
        }
        $album->setName($name);
        $album->setDescription($description);
        $album->setVisible($visible);
        return true;
    }

    public function albumDelete($id)
    {

        \Difra\Plugins\Gallery\Album::get($id)->delete();
    }

    public function albumUp($id)
    {

        \Difra\Plugins\Gallery\Album::get($id)->moveUp();
    }

    public function albumDown($id)
    {

        \Difra\Plugins\Gallery\Album::get($id)->moveDown();
    }

    /**
     * Изображения
 */

    /**
     * @param \DOMElement $albumNode
     * @param int $albumId
     * @return \Difra\Plugins\Gallery\Album
     */
    public function getImagesXML($albumNode, $albumId)
    {

        $album = \Difra\Plugins\Gallery\Album::get($albumId);
        if (!$album->load()) {
            return false;
        }
        $album->getXML($albumNode);
        $images = $album->getImages();
        if (!empty($images)) {
            foreach ($images as $image) {
                /** @var \DOMElement $imageNode */
                $imageNode = $albumNode->appendChild($albumNode->ownerDocument->createElement('image'));
                $imageNode->setAttribute('id', $image['id']);
            }
        }
        return $album;
    }

    public function imageAdd($albumId, $image)
    {

        $album = \Difra\Plugins\Gallery\Album::get($albumId);
        if (!$album->load()) {
            return false;
        }
        $album->addImage($image);
        return true;
    }

    public function imageDelete($albumId, $imageId)
    {

        $album = \Difra\Plugins\Gallery\Album::get($albumId);
        if (!$album->load()) {
            return false;
        }
        $album->delImage($imageId);
        return true;
    }

    public function imageUp($albumId, $imageId)
    {

        \Difra\Plugins\Gallery\Album::get($albumId)->imageUp($imageId);
    }

    public function imageDown($albumId, $imageId)
    {

        \Difra\Plugins\Gallery\Album::get($albumId)->imageDown($imageId);
    }
}
