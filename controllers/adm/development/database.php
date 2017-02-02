<?php

/**
 * Class AdmDevelopmentDatabaseController
 */
class AdmDevelopmentDatabaseController extends \Difra\Controller\Adm
{
    public function indexAction()
    {
        $node = $this->root->appendChild($this->xml->createElement('database'));
        // stats/mysql
        /** @var \DOMElement $mysqlNode */
        $mysqlNode = $node->appendChild($this->xml->createElement('mysql'));
        try {
            \Difra\MySQL\Parser::getStatusXML($mysqlNode);
        } catch (Exception $ex) {
            $mysqlNode->setAttribute('error', $ex->getMessage() . ': ' . \Difra\MySQL::getInstance()->getError());
        }
        // stats of Unify tables
        $unifyNode = $node->appendChild($this->xml->createElement('unify'));
        \Difra\Unify\DBAPI::getDbStatusXML($unifyNode);
    }
}
