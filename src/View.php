<?php

namespace Difra;

use Difra\View\HttpError;

/**
 * Class View
 * @package Difra
 */
class View
{
    /** @var bool Page rendered status */
    public static $rendered = false;
    /** @var string XSLT Resourcer instance */
    public static $instance = 'main';
    // options
    /** @var string Template instance */
    private $templateInstance = null;
    /** @var bool Echo output (return otherwise) */
    private $echo = true;
    /** @var bool Fill XML with various data */
    private $fillXML = true;
    /** @var bool Normalize output HTML */
    private $normalize = true;

    /**
     * Set template instance
     * @param string $templateInstance
     */
    public function setTemplateInstance(string $templateInstance)
    {
        $this->templateInstance = $templateInstance;
    }

    /**
     * Set echo flag
     * @param bool $echo
     */
    public function setEcho(bool $echo)
    {
        $this->echo = $echo;
    }

    /**
     * Set fill XML flag
     * @param bool $fillXML
     */
    public function setFillXML(bool $fillXML)
    {
        $this->fillXML = $fillXML;
    }

    /**
     * Set normalize flag
     * @param bool $normalize
     */
    public function setNormalize(bool $normalize)
    {
        $this->normalize = $normalize;
    }

    /**
     * Render
     */
    public function process(&$xml)
    {
        if ($this->templateInstance) {
            $instance = $this->templateInstance;
        } elseif (self::$instance) {
            $instance = self::$instance;
        } else {
            $instance = 'main';
        }
        Debugger::addLine("Render start (instance '$instance')");

        if (!$resource = Resourcer::getInstance('xslt')->compile($instance)) {
            throw new Exception("XSLT resource not found");
        }

        $time = microtime(true);
        $xslDom = new \DomDocument;
        $xslDom->resolveExternals = true;
        $xslDom->substituteEntities = true;
        if (!$xslDom->loadXML($resource)) {
            throw new Exception("XSLT load problem for instance '$instance'");
        }
        Debugger::addLine('XSLT XML loaded in ' . round(1000 * (microtime(true) - $time), 2) . 'ms');

        $time = microtime(true);
        $xslProcessor = new \XSLTProcessor();
        $xslProcessor->importStylesheet($xslDom);
        Debugger::addLine('XSLTProcessor initialized in ' . round(1000 * (microtime(true) - $time), 2) . 'ms');

        if ($this->fillXML and !HttpError::$error and !Debugger::$shutdown) {
            View\XML::fillXML($xml, $instance);
        }

        // transform template
        if ($html = $xslProcessor->transformToDoc($xml)) {
            if ($this->normalize) {
                $html = self::normalize($html);
            } else {
                $html->formatOutput = true;
                $html = $html->saveXML();
            }

            if (!$this->echo) {
                return $html;
            }

            echo $html;
            self::$rendered = true;
            if (Debugger::isEnabled()) {
                echo '<!-- Page rendered in ' . Debugger::getTimer() . ' seconds -->';
            }
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        } else {
            $errormsg = libxml_get_errors(); //error_get_last();
            throw new Exception($errormsg ? $errormsg['message'] : "Can't render templates");
        }
        return true;
    }

    /**
     * @param \DOMDocument $xml
     * @param bool|string $specificInstance
     * @param bool $dontEcho
     * @param bool $dontFillXML
     * @param bool $normalize
     * @return bool|string
     * @throws Exception
     * @deprecated
     */
    public static function render(
        &$xml,
        $specificInstance = false,
        $dontEcho = false,
        $dontFillXML = false,
        $normalize = true
    ) {
        $view = new self;
        $view->setTemplateInstance($specificInstance);
        $view->setEcho(!$dontEcho);
        $view->setFillXML(!$dontFillXML);
        $view->setNormalize($normalize);
        return $view->process($xml);
    }

    /**
     * XSLT to HTML5 covertation
     * @param $htmlDoc
     * @return string
     */
    public static function normalize($htmlDoc)
    {
        $normalizerXml = View\Normalizer::getXML();
        $normalizerDoc = new \DOMDocument();
        $normalizerDoc->loadXML($normalizerXml);
        $normalizerProc = new \XSLTProcessor();
        $normalizerProc->importStylesheet($normalizerDoc);
        return $normalizerProc->transformToXml($htmlDoc);
    }

    /**
     * HTTP redirect
     * @param $url
     */
    public static function redirect($url)
    {
        self::$rendered = true;
        header('Location: ' . $url);
        die();
    }

    /**
     * Add Expires and X-Accel-Expires headers
     * @param $ttl
     */
    public static function addExpires($ttl)
    {
        header('Expires: ' . gmdate('D, d M Y H:i:s', $ttl ? (time() + $ttl) : 0));
        if (isset($_SERVER['SERVER_SOFTWARE']) and substr($_SERVER['SERVER_SOFTWARE'], 0, 5) == 'nginx') {
            header('X-Accel-Expires: ' . ($ttl ? $ttl : 'off'));
        }
    }
}
