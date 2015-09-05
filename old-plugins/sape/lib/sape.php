<?php

namespace Difra\Plugins;

class SAPE
{
    public static function addXML()
    {

        $controller = \Difra\Envi\Action::getController();
        $sapeNode = $controller->realRoot->appendChild($controller->xml->createElement('sape'));
        $sapeNode->setAttribute('html', \Difra\Plugins\SAPE\Links::getHTML());
    }

    public static function addSitemapHTML()
    {

        $html = \Difra\Libs\XML\Sitemap::getXMLforHTML();
        if (!$html) {
            return;
        }
        $controller = \Difra\Envi\Action::getController();
        $sitemapNode = $controller->realRoot->appendChild($controller->xml->createElement('sitemap'));
        $sitemapNode->setAttribute('html', $html);
    }
}
