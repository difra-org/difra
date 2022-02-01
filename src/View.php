<?php

declare(strict_types=1);

namespace Difra;

use Difra\View\HTML\Element\HTML;
use Difra\View\HttpError;
use JetBrains\PhpStorm\NoReturn;

/**
 * Class View
 * @package Difra
 */
class View
{
    /** @var bool Page rendered status */
    public static bool $rendered = false;
    /** @var string XSLT Resourcer instance */
    public static string $instance = 'main';
    // options
    /** @var string|null Template instance */
    private ?string $templateInstance;
    /** @var bool Echo output (return otherwise) */
    private bool $echo = false;
    /** @var int Fill XML with various data */
    private int $fillXML = self::FILL_XML_NONE;
    /** @var bool Normalize output HTML */
    private bool $normalize = true;
    /** @var \XSLTProcessor|null */
    private ?\XSLTProcessor $xslProcessor = null;
    /** @var ?HTML HTML */
    private ?HTML $html = null;

    public const FILL_XML_NONE = 0;
    public const FILL_XML_LOCALE = 1;
    public const FILL_XML_MENU = 1 << 1;
    public const FILL_XML_OTHER = 1 << 2;
    public const FILL_XML_ALL = self::FILL_XML_LOCALE | self::FILL_XML_MENU | self::FILL_XML_OTHER;
    public const FILL_XML_PAGE = self::FILL_XML_ALL;

    /**
     * View constructor
     */
    public function __construct()
    {
        $this->templateInstance = self::$instance ?: 'main';
    }

    /**
     * Set template instance
     * @param string $templateInstance
     * @return View
     */
    public function setTemplateInstance(string $templateInstance): static
    {
        if ($this->templateInstance !== $templateInstance) {
            $this->templateInstance = $templateInstance;
            $this->xslProcessor = null;
        }
        return $this;
    }

    /**
     * Set echo flag
     * @param bool $echo
     * @return View
     */
    public function setEcho(bool $echo): static
    {
        if ($this->echo !== $echo) {
            $this->echo = $echo;
            $this->xslProcessor = null;
        }
        return $this;
    }

    /**
     * Set fill XML flag
     * @param int|bool $fillXML
     * @return View
     */
    public function setFillXML(int|bool $fillXML): static
    {
        // backwards compatibility
        if ($fillXML === true) {
            $fillXML = self::FILL_XML_ALL;
        } elseif ($fillXML === false) {
            $fillXML = self::FILL_XML_NONE;
        }

        if ($this->fillXML !== $fillXML) {
            $this->fillXML = $fillXML;
            $this->xslProcessor = null;
        }
        return $this;
    }

    /**
     * Set normalize flag
     * @param bool $normalize
     * @return View
     */
    public function setNormalize(bool $normalize): static
    {
        if ($this->normalize !== $normalize) {
            $this->normalize = $normalize;
            $this->xslProcessor = null;
        }
        return $this;
    }

    /**
     * Render
     * @param \DOMDocument|\DOMElement $xml
     * @return bool|string
     * @throws \Difra\Exception
     */
    public function process(\DOMDocument|\DOMElement $xml): bool|string
    {
        Debugger::addLine("Render start (instance '$this->templateInstance')");

        if (is_null($this->xslProcessor)) {
            if (!$resource = Resourcer::getInstance('xslt')->compile($this->templateInstance)) {
                throw new Exception('XSLT resource not found');
            }

            $time = microtime(true);
            $xslDom = new \DomDocument();
            $xslDom->resolveExternals = true;
            $xslDom->substituteEntities = true;
            if (!$xslDom->loadXML($resource)) {
                throw new Exception("XSLT load problem for instance '$this->templateInstance'");
            }
            Debugger::addLine('XSLT XML loaded in ' . round(1000 * (microtime(true) - $time), 2) . 'ms');

            $time = microtime(true);
            $this->xslProcessor = new \XSLTProcessor();
            $this->xslProcessor->importStylesheet($xslDom);
            Debugger::addLine('XSLTProcessor initialized in ' . round(1000 * (microtime(true) - $time), 2) . 'ms');
        }

        $this->html?->getXML($xml->documentElement);

        if (!HttpError::$error and !Debugger::$shutdown) {
            View\XML::fillXML($xml, $this->templateInstance, $this->fillXML);
        }

        // transform template
        if ($html = $this->xslProcessor->transformToDoc($xml)) {
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
            $errorMessage = libxml_get_errors(); //error_get_last();
            throw new Exception($errorMessage ? $errorMessage['message'] : "Can't render templates");
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
        \DOMDocument $xml,
        bool|string $specificInstance = false,
        bool $dontEcho = false,
        bool $dontFillXML = false,
        bool $normalize = true
    ): bool|string {
        $view = new self();
        $view->setTemplateInstance($specificInstance);
        $view->setEcho(!$dontEcho);
        $view->setFillXML(!$dontFillXML);
        $view->setNormalize($normalize);
        return $view->process($xml);
    }

    /**
     * Convert XHTML to HTML5
     * @param \DOMDocument $htmlDoc
     * @return string
     */
    public static function normalize(\DOMDocument $htmlDoc): string
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
     * @param string $url
     * @param ?bool $permanent
     */
    #[NoReturn]
    public static function redirect(string $url, ?bool $permanent = null): void
    {
        self::$rendered = true;
        if ($permanent) {
            http_response_code(301);
        }
        header('Location: ' . $url);
        die();
    }

    /**
     * Add Expires and X-Accel-Expires headers
     * @param $ttl
     */
    public static function addExpires($ttl)
    {
        header('Expires: ' . gmdate('D, d M Y H:i:s', $ttl ? (time() + $ttl) : 0) . ' GMT');
        if (isset($_SERVER['SERVER_SOFTWARE']) and str_starts_with($_SERVER['SERVER_SOFTWARE'], 'nginx')) {
            header('X-Accel-Expires: ' . ($ttl ?: 'off'));
        }
    }

    /**
     * @param string $instance
     * @param string $rootNodeName
     * @return bool|string
     * @throws \Difra\Exception
     * @deprecated
     */
    public static function simpleTemplate(string $instance, string $rootNodeName): bool|string
    {
        $xml = new \DOMDocument();
        $xml->appendChild($xml->createElement($rootNodeName));
        $view = new static();
        $view->setTemplateInstance($instance);
        return $view->process($xml);
    }

    /**
     * @param \DOMElement $node
     * @param string $template
     * @return bool|string
     * @throws \Difra\Exception
     */
    public static function simpleRender(\DOMElement $node, string $template = 'modals'): bool|string
    {
        $view = new static();
        $view->setFillXML(false);
        $view->setEcho(false);
        $view->setTemplateInstance($template);
        $view->setNormalize(false);
        return $view->process($node);
    }

    /**
     * @param \Difra\View\HTML\Element\HTML|null $html
     */
    public function setHTML(?HTML $html): void
    {
        $this->html =& $html;
    }
}
