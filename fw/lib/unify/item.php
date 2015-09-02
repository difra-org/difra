<?php

namespace Difra\Unify;

use Difra\Exception;
use Difra\MySQL;

/**
 * Class Item
 *
 * @package Difra\Unify
 */
abstract class Item extends DBAPI
{
    /** @var null|array Default search conditions */
    static protected $defaultSearch = null;
    static protected $defaultOrder = [];
    static protected $defaultOrderDesc = [];
    /** @var string[string $objKey][bool $full][] List of keys to load */
    protected static $_objKeys = [];
    protected $_data = null;
    protected $_full = false;
    protected $_modified = [];
    protected $_tempPrimary = null;
    /**
     * Methods to override
     */
    protected $_new = false;
    private $_saveImages = [];

    /**
     * Get default search conditions
     *
     * @return array|null
     */
    public static function getDefaultSearchConditions()
    {
        return static::$defaultSearch;
    }

    /**
     * Create new item
     *
     * @return static
     */
    public static function create()
    {

        $obj = new static(true);
        $obj->_new = true;
        return $obj;
    }

    /**
     * Get object by primary
     *
     * @param $primary
     * @return Item
     */
    public static function get($primary)
    {
        $objKey = static::getObjKey();
        if (isset(self::$objects[$objKey][$primary])) {
            return self::$objects[$objKey][$primary];
        }
        $o = new static;
        /** @var $o self */
        $o->_tempPrimary = $primary;
        if (!isset(self::$objects[$objKey])) {
            self::$objects[$objKey] = [];
        }
        self::$objects[$objKey][$primary] = $o;
        return $o;
    }

    /**
     * Get (first) object by field value
     *
     * @param string $field
     * @param string $value
     * @return static
     */
    public static function getByField($field, $value)
    {
        $objKey = static::getObjKey();
        $o = new static;
        /** @var $o self */
        try {
            $o->loadByField($field, (string)$value);
        } catch (Exception $e) {
            unset($o);
            return null;
        }
        if ($primary = $o->getPrimaryValue()) {
            if (!isset(self::$objects[$objKey])) {
                self::$objects[$objKey] = [];
            }
            if (!isset(self::$objects[$objKey][$primary])) {
                return self::$objects[$objKey][$primary] = $o;
            } else {
                // this object already exists
                unset($o);
                return self::$objects[$objKey][$primary];
            }
        }
        return $o;
    }

    /**
     * Get default sort order
     *
     * @return array
     */
    public static function getDefaultOrder()
    {
        return static::$defaultOrder;
    }

    /**
     * Get default descending order fields list
     *
     * @return array
     */
    public static function getDefaultOrderDesc()
    {
        return static::$defaultOrderDesc;
    }

    /**
     * Destructor
     */
    final public function __destruct()
    {
        $this->save();
    }

    /**
     * Save object data
     *
     * @param bool $replace Make replace instead of insert
     * @throws Exception
     */
    public function save($replace = false)
    {
        if (!$this->_new and empty($this->_modified)) {
            return;
        }
        $this->beforeSave();
        $this->_saveImages = [];
        $where = [];
        $db = MySQL::getInstance();
        // form request
        if (!$this->_new) {
            if (!$primary = $this->getPrimaryValue()) {
                throw new Exception('I don\'t know how to update Unify Item without primary value.');
            }
            $query = 'UPDATE `' . $db->escape($this->getTable()) . '`';
            $where[] = '`' . $db->escape($this->getPrimary()) . "`='" . $db->escape($primary) . "'";
        } else {
            $query = ($replace ? 'REPLACE INTO `' : 'INSERT INTO `') . $this->getTable() . '`';
        }
        // set
        //$mod = $db->escape( $this->_modified );
        $set = [];
        foreach ($this->_modified as $name => $property) {
            // remember properties that need images to be saved
            if (is_object($property) and method_exists($property, 'saveImages')) {
                /** @var $property \Difra\Param\AjaxHTML */
                if ($this->_new) {
                    if ($replace) {
                        throw new Exception('Replace is prohibited for objects with images');
                    }
                    $set[] = '`' . $db->escape($name) . "`='" . $db->escape($property->val(true)) . "'";
                    $this->_saveImages[$name] = $property;
                } else {
                    $property->saveImages(
                        DIR_DATA . '/u/' . $this->getObjKey() . "/{$name}/" . $this->getPrimaryValue(),
                        '/u/' . $this->getObjKey() . "/{$name}/" . $this->getPrimaryValue()
                    );
                    $set[] = '`' . $db->escape($name) . "`='" . $db->escape($property) . "'";
                }
            } elseif (is_array($property)) {
                $set[] = '`' . $db->escape($name) . "`='" . $db->escape(serialize($property)) . "'";
            } else {
                $set[] = '`' . $db->escape($name) . "`='" . $db->escape($property) . "'";
            }
        }
        if (!empty($set)) {
            $query .= ' SET ' . implode(',', $set);
        }
        // where
        if (!empty($where)) {
            $query .= ' WHERE ' . implode(' AND ', $where);
        }
        // make query
        $db->query($query);
        // get primary for new object
        if ($this->_new and $this->getPrimary()) {
            $this->_full = true;
            $this->_tempPrimary = $db->getLastId();
            /** @var $objKey string */
            self::$objects[static::getObjKey()][$this->_tempPrimary] = $this;
        }
        $this->_new = false;
        if (!empty($this->_saveImages)) {
            // we have images to save
            $this->_modified = $this->_saveImages;
            $this->_saveImages = [];
            $this->save();
        } else {
            $this->_modified = [];
        }
    }

    /**
     * This method is called before save().
     * Please note it might be called twice.
     */
    protected function beforeSave()
    {
    }

    /**
     * Get primary key value
     *
     * @return mixed|null
     */
    public function getPrimaryValue()
    {
        return isset($this->_data[$pri = static::getPrimary()]) ? $this->_data[$pri] : $this->_tempPrimary;
    }

    /**
     * Get object name
     *
     * @return string
     */
    public static function getObjKey()
    {
        static $objKey = null;
        if (!is_null($objKey)) {
            return $objKey;
        }
        return $objKey = implode(static::getClassParts());
    }

    /**
     * Chops namespace and class into parts without common pieces
     *
     * @return array
     * @throws Exception
     */
    protected static function getClassParts()
    {
        static $parts = null;
        if (!is_null($parts)) {
            return $parts;
        }
        $parts = explode('\\', $class = get_called_class());
        if (sizeof($parts) == 2 and $parts[0] == 'Objects') {
            unset($parts[0]);
            return $parts;
        } elseif (sizeof($parts) >= 4 and $parts[0] == 'Difra' and $parts[1] == 'Plugins' and $parts[3] == 'Objects') {
            unset($parts[3]);
            unset($parts[1]);
            unset($parts[0]);
            return $parts;
        }
        throw new Exception('Bad object class name: ' . $class);
    }

    /**
     * Get field value
     *
     * @param $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
        if (!isset(static::getColumns()[$name])) {
            throw new Exception("Object '" . static::getObjKey() . "' has no property '$name'.");
        }
        $this->load(
            isset(static::$propertiesList[$name]['autoload']) ? !static::$propertiesList[$name]['autoload'] : false
        );
        return isset($this->_data[$name]) ? $this->_data[$name] : null;
    }

    /**
     * Set field value
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        // html objects handling
        if (is_object($current = $this->$name) and $current::type == 'html') {
            $currentValue = $current->val(true);
        } else {
            $currentValue = $current;
        }
        if (is_object($value) and $value::type == 'html') {
            $newValue = $value->val(true);
        } else {
            $newValue = $value;
        }

        // not modified property, no need to update
        if ($currentValue == $newValue) {
            return;
        }

        // set update data
        $this->_data[$name] = $value;
        $this->_modified[$name] = $value;
    }

    /**
     * Load data
     *
     * @param bool $full Включать ли поля с autoload=false
     */
    public function load($full = false)
    {
        if ($primary = $this->getPrimaryValue()) {
            $this->loadByField(static::getPrimary(), $primary, $full);
        }
        $this->afterLoad();
    }

    /**
     * Load element by field value (for getByField())
     *
     * @param string $field
     * @param mixed  $value
     * @param bool   $full
     * @throws Exception
     */
    protected function loadByField($field, $value, $full = false)
    {
        if ($this->_full) {
            return;
        }
        if (!is_null($this->_data)) {
            if (!$full) {
                return;
            } else {
                $full = 'only';
            }
        }
        $db = MySQL::getInstance();
        $data = $db->fetchRow(
            'SELECT `' .
            implode('`,`', $db->escape(static::getKeys($full))) .
            '` FROM `' .
            $db->escape(static::getTable()) .
            '`'
            .
            ' WHERE `' .
            $db->escape($field) .
            "`='" .
            $db->escape($value) .
            "'"
        );
        if (empty($data)) {
            throw new Exception(
                "No such object: '" . static::getObjKey() . "' with `" . $field . "`='" . $value . "'."
            );
        }
        $this->_full = $full ? true : false;
        if (is_null($this->_data)) {
            $this->_data = $data;
        } else {
            foreach ($data as $k => $v) {
                $this->_data[$k] = $v;
            }
        }
    }

    /**
     * Get fields list (wrapper for getKeysArray)
     *
     * @param bool $full |'only'        Include autoload=false keys
     * @return array
     */
    public static function getKeys($full = true)
    {
        $objKey = static::getObjKey();
        if (isset(static::$_objKeys[$objKey][$full])) {
            return static::$_objKeys[$objKey][$full];
        }
        if (!isset(static::$_objKeys[$objKey])) {
            static::$_objKeys[$objKey] = [];
        }
        return static::$_objKeys[$objKey][$full] = static::getKeysArray($full);
    }

    /**
     * Get fields list
     *
     * @param bool $full |'only'        Include autoload=false keys
     * @return array
     */
    private static function getKeysArray($full = true)
    {
        $keys = [];
        foreach (static::getColumns() as $name => $prop) {
            if (!$full and isset($prop['autoload']) and !$prop['autoload']) {
                continue;
            }
            if ($full === 'only' and (!isset($prop['autoload']) or $prop['autoload'])) {
                continue;
            }
            $keys[] = $name;
        }
        return $keys;
    }

    /**
     * This method is called after load().
     */
    protected function afterLoad()
    {
    }

    /**
     * Get object data as XML node attributes
     *
     * @param \DOMNode|\DOMElement $node
     */
    public function getXML($node)
    {
        $this->load();
        if (empty($this->_data)) {
            return;
        }
        foreach ($this->_data as $k => $v) {
            $node->setAttribute($k, $v);
        }
        $this->postProcessXML($node);
    }

    /**
     * This method is called after getXML is done
     *
     * @param $node
     */
    protected function postProcessXML($node)
    {
    }

    /**
     * Delete item
     */
    public function delete()
    {
        $this->_new = false;
        $this->_modified = [];
        if ($primary = $this->getPrimaryValue()) {
            $db = MySQL::getInstance();
            $db->query(
                'DELETE FROM `' . static::getTable()
                . '` WHERE `' . $db->escape($this->getPrimary()) . '`=\'' . $db->escape($primary) . '\''
            );
        }
    }

    /**
     * Quietly set data (e.g. on item load)
     *
     * @param $newData
     */
    public function setData($newData)
    {
        $this->_data = $newData;
    }
}
