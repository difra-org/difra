<?php

namespace Difra\MySQL\SQL;

use Difra\Exception;
use Difra\Libs\Diff;
use Difra\MySQL\Parser;

/**
 * Class Table
 * @package Difra\MySQL\SQL
 * @deprecated
 */
class Table extends Common
{
    private $name = '';
    private $definitions = [];
    private $goal = [];

    /**
     * Create new table object
     * @param array $chunks
     * @return Table
     */
    public static function create($chunks = null)
    {
        $table = new self;
        if ($chunks) {
            $table->loadChunks($chunks, false);
        }
        return $table;
    }

    /**
     * Get table object by name
     * @param $name
     * @return null
     */
    public static function getByName($name)
    {
        return isset(self::$list[$name]) ? self::$list[$name] : null;
    }

    /**
     * Load table from chopped SQL
     * @param array $chunks
     * @param bool $goal
     * @throws Exception
     */
    public function loadChunks($chunks, $goal = true)
    {
        // skip CREATE TABLE
        if ($chunks[0] != 'CREATE' or $chunks[1] != 'TABLE') {
            throw new Exception('Expected to get CREATE TABLE chunks');
        }
        array_shift($chunks);
        array_shift($chunks);
        // table name
        $this->name = self::chunk2name(array_shift($chunks));
        self::$list[$this->name] = $this;
        // get columns and list definitions
        $definitions = self::getDefinitions($chunks);
        if (!$goal) {
            $this->definitions = $definitions;
        } else {
            $this->goal = $definitions;
        }
    }

    /**
     * @param $chunks
     * @throws Exception
     */
    public static function autoGoal($chunks)
    {
        if ($chunks[0] != 'CREATE' or $chunks[1] != 'TABLE') {
            throw new Exception('Expected to get CREATE TABLE chunks');
        }
        $name = self::chunk2name($chunks[2]);
        if (!$o = self::getByName($name)) {
            $o = self::create();
        }
        if ($o) {
            $o->loadChunks($chunks);
        }
    }

    /**
     * @param $chunks
     * @return array
     * @throws Exception
     */
    private static function getDefinitions(&$chunks)
    {
        if ($chunks[0] != '(') {
            throw new Exception('Expected \'(\' after CREATE TABLE `...`');
        }
        array_shift($chunks);
        $lines = [];
        $line = [];
        $d = 0;
        while (!empty($chunks)) {
            $a = array_shift($chunks);
            if ($a == '(') {
                $d++;
            } elseif ($d == 0 and $a == ')') {
                if (!empty($line)) {
                    $lines[] = $line;
                    $line = [];
                }
                break;
            } elseif ($a == ')') {
                $d--;
            } elseif ($d == 0 and $a == ',') {
                if (!empty($line)) {
                    $lines[] = $line;
                    $line = [];
                }
                continue;
            }
            $line[] = $a;
        }
        if (!empty($line) or empty($lines)) {
            throw new Exception('Definitions parse error');
        }
        return $lines;
    }

    /**
     * @param \DOMElement $node
     */
    public function getStatusXML($node)
    {
        /** @var $statusNode \DOMElement */
        $statusNode = $node->appendChild($node->ownerDocument->createElement('table'));
        $statusNode->setAttribute('name', $this->name);
        if (empty($this->definitions)) {
            $statusNode->setAttribute('nodef', 1);
            return;
        }
        if (empty($this->goal)) {
            $statusNode->setAttribute('nogoal', 1);
            return;
        }
        $current = [];
        foreach ($this->definitions as $def) {
            $current[] = Parser::def2string($def);
        }
        $goal = [];
        foreach ($this->goal as $g) {
            $goal[] = Parser::def2string($g);
        }
        $diff = Diff::diffArrays($current, $goal);
        if (is_array($diff) and !empty($diff)) {
            $haveDiff = false;
            foreach ($diff as $d) {
                if ($d['sign'] != '=') {
                    $haveDiff = true;
                    break;
                }
            }
            if (!$haveDiff) {
                return;
            }
            $statusNode->setAttribute('diff', 1);
            foreach ($diff as $d) {
                /** @var $diffNode \DOMElement */
                $diffNode = $statusNode->appendChild($node->ownerDocument->createElement('diff'));
                @$diffNode->setAttribute('sign', $d['sign']);
                @$diffNode->setAttribute('value', $d['value']);
            }
        }
    }
}
