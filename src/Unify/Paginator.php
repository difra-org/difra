<?php

namespace Difra\Unify;

use Difra\Envi\Action;
use Difra\Exception;
use JetBrains\PhpStorm\Pure;

/**
 * Paginator
 * Class Paginator
 * @package Difra\Unify
 */
class Paginator
{
    /** @var int Items per page */
    protected $perpage = 20;
    /** @var int|null Current page */
    protected $page = 1;
    /** @var int Total items number */
    protected $total = null;
    /** @var int Pages number */
    protected $pages = null;
    /** @var string Link prefix */
    protected $linkPrefix = '';
    /** @var string|bool Character for get parameter */
    protected $get = false;

    /**
     * Constructor
     */
    #[Pure]
    public function __construct()
    {
        $this->linkPrefix = Action::getControllerUri();
    }

    /**
     * Return LIMIT values for SQL
     * @return array
     */
    public function getPaginatorLimit()
    {
        return [($this->page - 1) * $this->perpage, $this->perpage];
    }

    #[Pure]
    public function getSQL()
    {
        $limit = $this->getPaginatorLimit();
        return " LIMIT $limit[0],$limit[1] ";
    }

    /**
     * Set total elements number
     * @param int $count
     */
    public function setTotal($count)
    {
        $this->total = $count;
        $this->pages = floor(($count - 1) / $this->perpage) + 1;
    }

    /**
     * Get pages number
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Add paginator node to XML
     * @param \DOMNode $node
     */
    public function getPaginatorXML($node)
    {
        /** @var \DOMElement $pNode */
        $pNode = $node->appendChild($node->ownerDocument->createElement('paginator'));
        $pNode->setAttribute('page', $this->page);
        $pNode->setAttribute('pages', $this->pages);
        $pNode->setAttribute('link', $this->linkPrefix);
        $pNode->setAttribute('get', $this->get);
    }

    /**
     * Set current page number
     * @param int $page
     * @throws Exception
     */
    public function setPage(int $page)
    {
        if ($page < 1) {
            throw new Exception("Expected page number as parameter");
        }
        $this->page = $page;
    }

    /**
     * Get current page number
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Set GET character
     * false -> $linkPrefix/page/$page
     * '?' -> $linkPrefix?page=$page
     * '&' -> $linkPrefix&page=$page
     * etc.
     * @param string|false $get
     */
    public function setGet($get)
    {
        $this->get = $get;
    }

    /**
     * Links prefix
     * @param string $linkPrefix
     */
    public function setLinkPrefix($linkPrefix)
    {
        $this->linkPrefix = $linkPrefix;
    }

    /**
     * Get links prefix
     * @return string
     */
    public function getLinkPrefix()
    {
        return $this->linkPrefix;
    }

    /**
     * Set items number per page
     * @param int $perpage
     */
    public function setPerpage($perpage)
    {
        $this->perpage = $perpage;
    }
}
