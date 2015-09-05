<?php

namespace Difra\Plugins\Catalog\Item;

class Image
{
    private $id = null;
    private $item = null;
    private $main = null;

    public static function loadFromArray($data)
    {

        $image = new self;
        $image->id = $data['id'];
        $image->item = $data['item'];
        $image->main = $data['main'] ? true : false;
    }
}
