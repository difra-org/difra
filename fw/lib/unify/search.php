<?php

namespace Difra\Unify;

use Difra\Unify;

/**
 * Class Search
 *
 * @package Difra\Unify
 */
class Search extends Query
{
    /**
     * Get list
     *
     * @return Unify[]
     */
    public function getList()
    {
        $result = $this->doQuery();
        if (empty($result)) {
            return null;
        }
        foreach ($result as $k => $v) {
            $primary = $v->getPrimaryValue();
            if (!isset(Unify::$objects[$this->objKey])) {
                Unify::$objects[$this->objKey] = [];
            }
            if (isset(Unify::$objects[$this->objKey][$primary])) {
                $result[$k] = Unify::$objects[$this->objKey][$primary];
            } else {
                $result[$k] = Unify::$objects[$this->objKey][$primary] = $v;
            }
        }
        return $result;
    }

    /**
     * Add list as XML
     *
     * @param \DOMNode $toNode
     */
    public function getListXML($toNode)
    {
        /** @var \DOMElement $node */
        $node = $toNode->appendChild($toNode->ownerDocument->createElement($this->objKey . 'List'));
        $list = $this->getList();
        if (empty($list)) {
            $node->setAttribute('empty', 1);
        } else {
            foreach ($list as $item) {
                $itemNode = $node->appendChild($toNode->ownerDocument->createElement($this->objKey));
                /** @var $item Unify */
                $item->getXML($itemNode);
            }
        }
        if ($this->page) {
            $this->getPaginatorXML($node);
        }
    }
}
