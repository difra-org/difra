<?php

namespace Difra\Plugins\Announcements;

use Difra\Cache;
use Difra\MySQL;

Class Category
{
    private $id = null;
    private $textName = null;
    private $category = null;
    private $loaded = true;
    private $modified = false;

    public static function create($id = null)
    {
        $Categoty = new self;
        $Categoty->id = $id;
        return $Categoty;
    }

    /**
     * Чистит кэш
     * @static
     */
    private static function cleanCache()
    {
        Cache::getInstance()->remove('announcements_category');
    }

    /**
     * Проверяет есть ли уже такая категория
     * @param $category
     * @return bool
     */
    public static function checkName($category)
    {
        $res = Cache::getInstance()->get('announcements_category');
        if ($res) {
            foreach ($res as $k => $data) {
                if ($data['category'] == $category) {
                    return true;
                }
            }
            return false;
        } else {
            $db = MySQL::getInstance();
            $res = $db->fetchOne(
                "SELECT `id` FROM `announcements_category` WHERE `category`='" . $db->escape($category) . "'"
            );
            return !empty($res) ? true : false;
        }
    }

    /**
     * Возвращает категорию по её ссылке
     * @param $categoryLink
     * @return bool|Category
     */
    public static function getNameByLink($categoryLink)
    {

        $res = Cache::getInstance()->get('announcements_category');
        if ($res) {
            foreach ($res as $k => $data) {
                if ($data['category'] == $categoryLink) {
                    $Category = new self;
                    $Category->id = $data['id'];
                    $Category->textName = $data['categoryText'];
                    $Category->category = $data['category'];
                    $Category->modified = false;
                    return $Category;
                }
            }
        } else {
            $db = MySQL::getInstance();
            $res = $db->fetchRow(
                "SELECT * FROM `announcements_category` WHERE `category`='" . $db->escape($categoryLink) . "'"
            );
            if (!empty($res)) {
                $Category = new self;
                $Category->id = $res['id'];
                $Category->textName = $res['categoryText'];
                $Category->category = $res['category'];
                $Category->modified = false;
                return $Category;
            }
        }
        return false;
    }

    /**
     * Возвращает название категории
     */
    public function getName()
    {
        return $this->textName;
    }

    /**
     * Возвращает id категории
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Устанавливает текстовое название категории
     * @param $name
     */
    public function setTextName($name)
    {

        $this->textName = trim($name);
        $this->modified = true;
    }

    /**
     * Устанавливает категорию анонса
     * @param $category
     */
    public function setCategory($category)
    {

        $this->category = trim($category);
        $this->modified = true;
    }

    /**
     * Сохраняет или апдейтит категорию
     */
    private function save()
    {

        $db = MySQL::getInstance();

        if (!is_null($this->id)) {
            // update
            $query = "UPDATE `announcements_category` SET `category`='" . $db->escape($this->category) .
                     "', `categoryText`='" . $db->escape($this->textName) . "' WHERE `id`='" . intval($this->id) . "'";
        } else {
            // insert
            $query = "INSERT INTO `announcements_category` SET `category`='" . $db->escape($this->category) .
                     "', `categoryText`='" . $db->escape($this->textName) . "'";
        }

        $db->query($query);
    }

    /**
     * Возвращает в xml список всех категорий
     * @static
     * @param \DOMNode|\DOMElement $node
     */
    public static function getList($node)
    {

        $Cache = Cache::getInstance();

        $res = $Cache->get('announcements_category');

        if (!$res) {
            $db = MySQL::getInstance();
            $query = "SELECT * FROM `announcements_category`";
            $res = $db->fetch($query);
        } else {
            $node->setAttribute('cached', true);
        }

        if (!empty($res)) {
            $saveToCache = null;
            foreach ($res as $k => $data) {
                $catNode = $node->appendChild($node->ownerDocument->createElement('category'));
                $catNode->setAttribute('id', $data['id']);
                $catNode->setAttribute('category', $data['category']);
                $catNode->setAttribute('name', $data['categoryText']);
                $saveToCache[$data['id']] = $data;
            }
            $Cache->put('announcements_category', $saveToCache, 10800);
        }
    }

    /**
     * Удаляет категорию
     * @static
     * @param $id
     */
    public static function delete($id)
    {

        MySQL::getInstance()->query("DELETE FROM `announcements_category` WHERE `id`='" . intval($id) . "'");
        self::cleanCache();
    }

    public function __destruct()
    {

        if ($this->modified && $this->loaded) {
            $this->save();
            self::cleanCache();
        }
    }
}
