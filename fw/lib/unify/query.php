<?php

namespace Difra\Unify;

use Difra\Exception;
use Difra\MySQL;
use Difra\Unify;

/**
 * Class Query
 * @package Difra\Unify
 */
class Query extends Paginator
{
    /** @var string Unify object name */
    public $objKey = null;
    /** @var array Search conditions */
    public $conditions = [];
    /** @var int LIMIT from value */
    public $limitFrom = null;
    /** @var int LIMIT number value */
    public $limitNum = null;
    /** @var string|string[] Sort order */
    private $order = null;
    private $orderDesc = [];
    /** @var string[]|self[] Unify objects or Query objects for JOIN */
    private $with = [];
    /** @var bool Get all data including autoload=false */
    public $full = false;

    /**
     * Constructor
     * @param string $objKey Unify object name
     * @throws Exception
     */
    public function __construct($objKey)
    {
        parent::__construct();
        $this->objKey = $objKey;
        if (!$class = Storage::getClass($objKey)) {
            throw new Exception('objKey \'' . $objKey . '\' does not exist.');
        }
        $this->order = $class::getDefaultOrder() ? (array)$class::getDefaultOrder() : null;
        $this->orderDesc = $class::getDefaultOrderDesc() ? (array)$class::getDefaultOrderDesc() : null;
    }

    /**
     * Do query
     * @return Unify[]|null
     */
    public function doQuery()
    {
//		try {
        $db = MySQL::getInstance();
        $result = $db->fetch($this->getQuery());
//		} catch( Exception $ex ) {
//			return null;
//		}
        if ($this->page) {
            $this->setTotal($db->getFoundRows());
        }
        if (empty($result)) {
            return null;
        }
        $res = [];
        $class = Unify::getClass($this->objKey);
        foreach ($result as $newData) {
            /** @var Item $o */
            $o = new $class;
            $o->setData($newData);
            $res[] = $o;
        }
        return $res;
    }

    /**
     * Get SQL query string
     * @return string
     */
    public function getQuery()
    {
        $q = 'SELECT ';
        if ($this->page) {
            $q .= 'SQL_CALC_FOUND_ROWS ';
        }

        $q .= $this->getSelectKeys();
        // TODO: JOIN keys (all joins should use objects' own methods to support multiple JOINS)
        $class = Unify::getClass($this->objKey);
        /** @var $class Item */
        $q .= " FROM `{$class::getTable()}`";
        // TODO: ... LEFT JOIN ... ON ...
        $q .= $this->getWhere();
        $q .= $this->getOrder();
        $q .= $this->getLimit();

        return $q;
    }

    /**
     * Get select fields list for SQL
     * @throws Exception
     * @return string
     */
    public function getSelectKeys()
    {
        $db = MySQL::getInstance();
        /** @var Unify $class */
        $class = Unify::getClass($this->objKey);
        if (!$class) {
            throw new Exception("Can't query unknown object '{$this->objKey}'");
        }
        $keys = $class::getKeys($this->full);
        $keys = $db->escape($keys);
        $keysS = [];
        $table = $db->escape($class::getTable());
        foreach ($keys as $key) {
            $keysS[] = "`$table`.`$key`";
        }
        return implode(',', $keysS);
    }

    /**
     * Get WHERE for SQL
     * @return string
     */
    public function getWhere()
    {
        $db = MySQL::getInstance();
        /** @var Unify $class */
        $class = Unify::getClass($this->objKey);
        $conditions = !empty($this->conditions) ? $this->conditions : $class::getDefaultSearchConditions();
        if (empty($conditions)) {
            return '';
        }
        $c = [];
        foreach ($conditions as $k => $v) {
            if (!is_numeric($k)) {
                $c[] = '`' . $db->escape($k) . "`='" . $db->escape($v) . "'";
            } else {
                $c[] = $v;
            }
        }
        return ' WHERE ' . implode(' AND ', $c);
    }

    /**
     * Get ORDER for SQL
     * @return string
     */
    public function getOrder()
    {
        if (empty($this->order)) {
            return '';
        }
        /** @var Unify $class */
        $class = Unify::getClass($this->objKey);
        $db = MySQL::getInstance();
        $table = $db->escape($class::getTable());
        $ord = ' ORDER BY ';
        $d = '';
        foreach ((array)$this->order as $column) {
            $ord .= "$d`$table`.`" . $db->escape($column) . '`' .
                    ((empty($this->orderDesc) or !in_array($column, $this->orderDesc)) ? '' : ' DESC');
            $d = ', ';
        }
        return $ord;
    }

    /**
     * Set sort order
     * Example: $columns = ['price','date','views'], $desc = ['date'] will produce:
     *        'ORDER BY `price`,`date` DESC,`views`
     * @param string|string[] $columns List of columns for sort
     * @param string|string[] $desc Which columns should sort descending
     */
    public function setOrder($columns = [], $desc = [])
    {
        if (!$columns or empty($columns)) {
            $this->order = null;
            $this->orderDesc = null;
        }
        $this->order = is_array($columns) ? $columns : [$columns];
        $this->orderDesc = is_array($desc) ? $desc : [$desc];
    }

    /**
     * Get LIMIT string for request
     * @return string
     */
    public function getLimit()
    {
        if ($this->page) {
            list($this->limitFrom, $this->limitNum) = $this->getPaginatorLimit();
        }

        if (!$this->limitFrom and !$this->limitNum) {
            return '';
        }
        $q = ' LIMIT ';
        if ($this->limitFrom) {
            $q .= intval($this->limitFrom) . ",";
        }
        if ($this->limitNum) {
            $q .= intval($this->limitNum);
        } else {
            $q .= '999999'; // чтобы задать только отступ в LIMIT, считаем это отсутсвтием лимита :)
        }
        return $q;
    }

    /**
     * Add search condition as key->value array
     * @param array $conditions
     * @throws Exception
     */
    public function addConditions($conditions)
    {
        if (!is_array($conditions)) {
            throw new Exception('Difra\Unify\Query->addConditions() accepts only array as parameter.');
        }
        if (empty($conditions)) {
            return;
        }
        foreach ($conditions as $k => $v) {
            $this->addCondition($k, $v);
        }
    }

    /**
     * Add search condition as key->value
     * @param string $column
     * @param string $value
     * @throws Exception
     */
    public function addCondition($column, $value)
    {
        $this->conditions[$column] = $value;
    }

    /**
     * Add search condition as string
     * String can contain complex conditions, but you should escape() all data yourself.
     * @param string|string[] $condition
     */
    public function addCustomConditions($condition)
    {
        if (!is_array($condition)) {
            $this->conditions[] = $condition;
        } else {
            foreach ($condition as $cond) {
                $this->addCustomConditions($cond);
            }
        }
    }

    /**
     * Add Unify Object or Unify Query to JOIN
     * @param string|self $query
     * @throws Exception
     */
    public function join($query)
    {
        if (is_string($query)) {
            $q = new self($query);
            $this->with[] = $q;
        } elseif ($query instanceof Query) {
            $this->with[] = $query;
        } else {
            throw new Exception("Expected string or Unify\\Query as a parameter");
        }
    }
}
