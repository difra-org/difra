<?php

declare(strict_types=1);

namespace Difra\Unify;

use Difra\Envi\Action;
use Difra\Exception;

/**
 * Paginator
 * Class Paginator
 * @package Difra\Unify
 */
class Paginator
{
    /** @var int Items per page */
    protected int $perpage = 20;
    /** @var int|null Current page */
    protected ?int $page = 1;
    /** @var int|null Total items number */
    protected ?int $total = null;
    /** @var int|null Pages number */
    protected ?int $pages = null;
    /** @var string|null Link prefix */
    protected ?string $linkPrefix = '';
    /** @var string|bool Character for get parameter */
    protected string|bool $get = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->linkPrefix = Action::getControllerUri();
    }

    /**
     * Return LIMIT values for SQL
     * @return array
     */
    public function getPaginatorLimit(): array
    {
        return [($this->page - 1) * $this->perpage, $this->perpage];
    }

    public function getSQL(): string
    {
        $limit = $this->getPaginatorLimit();
        return " LIMIT $limit[0],$limit[1] ";
    }

    /**
     * Set total elements number
     * @param int $count
     */
    public function setTotal(int $count)
    {
        $this->total = $count;
        $this->pages = floor(($count - 1) / $this->perpage) + 1;
    }

    /**
     * Get pages number
     * @return int|null
     */
    public function getPages(): ?int
    {
        return $this->pages;
    }

    /**
     * Add paginator node to XML
     * @param \DOMElement $node
     */
    public function getPaginatorXML(\DOMElement $node)
    {
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
     * @param bool|string $get
     */
    public function setGet(bool|string $get)
    {
        $this->get = $get;
    }

    /**
     * Links prefix
     * @param string $linkPrefix
     */
    public function setLinkPrefix(string $linkPrefix)
    {
        $this->linkPrefix = $linkPrefix;
    }

    /**
     * Get links prefix
     * @return string|null
     */
    public function getLinkPrefix(): ?string
    {
        return $this->linkPrefix;
    }

    /**
     * Set items number per page
     * @param int $perpage
     */
    public function setPerpage(int $perpage)
    {
        $this->perpage = $perpage;
    }
}
