<?php

namespace Difra\View;

use Difra\Auth;
use Difra\Controller;
use Difra\Debugger;
use Difra\Envi;
use Difra\Envi\Action;
use Difra\Envi\Request;
use Difra\Envi\Version;
use Difra\Locales;
use Difra\Resourcer;
use Difra\View;

/**
 * Class XML
 * @package Difra\View
 */
class XML
{
    /**
     * Fill output XML with some common data
     * @param \DOMDocument|null $xml
     * @param null $instance
     * @param int $fillXML
     */
    public static function fillXML(&$xml = null, $instance = null, $fillXML = View::FILL_XML_ALL)
    {
        if ($fillXML === View::FILL_XML_NONE) {
            return;
        }
        $controller = Controller::getInstance();
        if (is_null($xml)) {
            $xml = $controller->xml;
            $node = $controller->realRoot;
        } else {
            $node = $xml->documentElement;
        }
        Debugger::addLine('Filling XML data for render: Started');

        // locale
        if ($fillXML & View::FILL_XML_LOCALE) {
            Locales::getInstance()->getLocaleXML($node);
        }

        // menu
        if ($fillXML & View::FILL_XML_MENU) {
            if ($menuResource = Resourcer::getInstance('menu')->compile(View::$instance)) {
                $menuXML = new \DOMDocument();
                $menuXML->loadXML($menuResource);
                $node->appendChild($xml->importNode($menuXML->documentElement, true));
            }
        }

        // other
        if ($fillXML & View::FILL_XML_OTHER) {
            // TODO: sync this with Envi::getState()
            $node->setAttribute('lang', Envi\Setup::getLocale());
            $node->setAttribute('langShort', Envi\Setup::getLocaleLang());
            $node->setAttribute('site', Envi::getSubsite());
            $node->setAttribute('host', $host = Envi::getHost());
            $node->setAttribute('mainhost', $mainhost = Envi::getHost(true));
            $node->setAttribute('protocol', Envi::getProtocol());
            $node->setAttribute('fullhost', Envi::getURLPrefix());
            $node->setAttribute('instance', $instance ? $instance : View::$instance);
            $node->setAttribute('uri', Envi::getUri());
            $node->setAttribute('controllerUri', Action::getControllerUri());
            $node->setAttribute('actionUri', Action::getActionUri());
            if ($host != $mainhost) {
                $node->setAttribute('urlprefix', Envi::getURLPrefix(true));
            }
            // get user agent
            Envi\UserAgent::getUserAgentXML($node);
            // ajax flag
            $node->setAttribute(
                'ajax',
                (
                    Request::isAjax()
                    or
                    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage')
                ) ? '1' : '0'
            );
            $node->setAttribute(
                'switcher',
                (!$controller->cache
                 and isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                     and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'SwitchPage'
                ) ? '1' : '0'
            );
            // build and version number
            $node->setAttribute('build', Version::getBuild());
            $node->setAttribute('buildShort', Version::getBuild(true));
            $node->setAttribute('framework', Version::getFrameworkVersion());
            $node->setAttribute('frameworkLong', Version::getFrameworkVersion()); // todo: remove
            // date
            /** @var $dateNode \DOMElement */
            $dateNode = $node->appendChild($xml->createElement('date'));
            $dateKeys = ['d', 'e', 'A', 'a', 'm', 'B', 'b', 'Y', 'y', 'c', 'x', 'H', 'M', 'S'];
            $dateValues = explode('|', strftime('%' . implode('|%', $dateKeys)));
            $dateCombined = array_combine($dateKeys, $dateValues);
            $dateNode->setAttribute('ts', time());
            foreach ($dateCombined as $k => $v) {
                $dateNode->setAttribute($k, $v);
            }
            // debug flag
            $node->setAttribute('debug', Debugger::isEnabled() ? '1' : '0');
            // config values (for js variable)
            $configNode = $node->appendChild($xml->createElement('config'));
            Envi::getStateXML($configNode);
            // auth
            Auth::getInstance()->getAuthXML($node);
            // Add config js object
            $config = Envi::getState();
            $confJS = '';
            foreach ($config as $k => $v) {
                $confJS .= "config.{$k}='" . addslashes($v) . "';";
            }
            $node->setAttribute('jsConfig', $confJS);
        }

        Debugger::addLine('Filling XML data for render: Done');
        Debugger::debugXML($node);
    }
}
