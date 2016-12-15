<?php

namespace Difra\View;

use Difra\Ajaxer;
use Difra\Controller;
use Difra\Debugger;
use Difra\Envi\Request;
use Difra\Envi\UserAgent;
use Difra\View;

/**
 * Class Output
 * @package Difra\View
 */
class Output
{
    /** @var string */
    public static $output = null;
    /** @var string */
    public static $outputType = 'text/plain';

    /**
     * Choose view depending on request type
     */
    final public static function start()
    {
        $controller = Controller::getInstance();
        if (Controller::hasUnusedParameters()) {
            $controller->putExpires(true);
            throw new HttpError(404);
        } elseif (!is_null(self::$output)) {
            $controller->putExpires();
            header('Content-Type: ' . self::$outputType . '; charset="utf-8"');
            echo self::$output;
            View::$rendered = true;
        } elseif (Debugger::isEnabled() and isset($_GET['xml']) and $_GET['xml']) {
            if ($_GET['xml'] == '2') {
                View\XML::fillXML();
            }
            switch (UserAgent::getAgent()) {
                case UserAgent::AGENT_SAFARI:
                    header('Content-Type: text/plain; charset="utf-8"');
                    break;
                default:
                    header('Content-Type: text/xml; charset="utf-8"');
            }
            $controller->xml->formatOutput = true;
            $controller->xml->encoding = 'utf-8';
            echo rawurldecode($controller->xml->saveXML());
            View::$rendered = true;
        } elseif (!View::$rendered and Request::isAjax()) {
            $controller->putExpires();
            // should be application/json, but opera doesn't understand it and offers to save file to disk
            header('Content-type: text/plain');
            echo(Ajaxer::getResponse());
            View::$rendered = true;
        } elseif (!View::$rendered) {
            $controller->putExpires();
            try {
                View::render($controller->xml);
            } catch (HttpError $ex) {
                if (!Debugger::isConsoleEnabled()) {
                    throw new HttpError(HttpError::E_INTERNAL_SERVER_ERROR);
                } else {
                    echo Debugger::debugHTML(true);
                    die();
                }
            }
        }
    }
}
