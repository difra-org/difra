<?php

namespace Difra\Plugins\News;

use Difra, Difra\Plugins;

class Publication
{
    private $id = null;
    private $title = null;
    private $link = null;
    private $pubDate = null;
    private $viewDate = null;
    private $stopDate = null;
    private $visible = 1;
    private $important = 0;
    private $body = null;
    private $announcement = null;
    private $sourceName = null;
    private $sourceURL = null;
    private $modifiedDate = null;
    private $loaded = true;
    private $modified = false;

    public static function create($id = null)
    {

        $Pub = new self;
        if (!is_null($id)) {
            $Pub->id = intval($id);
        }
        return $Pub;
    }

    /**
     * Устанавливает заголовок новости
     * @param $title
     */
    public function setTitle($title)
    {

        $this->title = trim($title);
        $this->link = \Difra\Locales::getInstance()->makeLink(trim($title));
        $this->modified = true;
    }

    /**
     * Устанавливает дату публикации
     * @param string $date
     */
    public function setPubDate($date)
    {

        $this->pubDate = \Difra\Locales::getInstance()->getMysqlDate($date);
        $this->modified = true;
    }

    /**
     * Устанавливает дату начала демонстрации новости
     * @param string $date
     */
    public function setViewDate($date)
    {

        $this->viewDate = \Difra\Locales::getInstance()->getMysqlDate($date);
        $this->modified = true;
    }

    /**
     * Устанавливает флаг видимости новости
     * @param int $data
     */
    public function setVisible($data)
    {

        $this->visible = intval($data);
        $this->modified = true;
    }

    /**
     * Устанавливает флаг важности новости
     * @param int $data
     */
    public function setImportant($data)
    {

        $this->important = intval($data);
        $this->modified = true;
    }

    /**
     * Устанавливает дату завершения демонстрации новости
     * @param string $date
     */
    public function setStopDate($date)
    {

        $this->stopDate = \Difra\Locales::getInstance()->getMysqlDate($date);
        $this->modified = true;
    }

    /**
     * Устанавливает название источника
     * @param string $data
     */
    public function setSourceName($data)
    {

        $this->sourceName = trim($data);
        $this->modified = true;
    }

    /**
     * Устанавливает url источника
     * @param $data
     */
    public function setSourceURL($data)
    {

        $data = trim($data);
        if ($data != '' && substr($data, 0, 7) != 'http://') {
            $data = 'http://' . $data;
        }
        $this->sourceURL = $data;
        $this->modified = true;
    }

    /**
     * Устанавилвает текст новости
     * @param \Difra\Param\AjaxHTML $body
     */
    public function setBody($body)
    {

        $this->body = $body;
        $this->modified = true;
    }

    /**
     * Устанавливает анонс новости
     * @param \Difra\Param\AjaxHTML $announce
     */
    public function setAnnouncement($announce)
    {

        $this->announcement = $announce;
        $this->modified = true;
    }

    /**
     * Сохраняет объект новости в базе данных
     */
    private function save()
    {

        $db = \Difra\MySQL::getInstance();

        if (is_null($this->id)) {
            // добавление в базу

            $query = "INSERT INTO `news` (`link`, `title`, `pubDate`, `viewDate`, `stopDate`, `source`, `sourceURL`, `visible`, `important`)
                        VALUES ('" . $db->escape($this->link) . "', '" . $db->escape($this->title) . "', '" .
                     $db->escape($this->pubDate) .
                     "', '" . $db->escape($this->viewDate) . "', '" . $db->escape($this->stopDate) .
                     "', '" . $db->escape($this->sourceName) . "', '" . $db->escape($this->sourceURL) .
                     "', '" . $this->visible . "', '" . $this->important . "')";
        } else {
            // обновление записи

            $query = "UPDATE `news` SET `title`='" . $db->escape($this->title) .
                     "', `pubDate`='" . $db->escape($this->pubDate) .
                     "', `viewDate`='" . $db->escape($this->viewDate) .
                     "', `stopDate`='" . $db->escape($this->stopDate) .
                     "', `source`='" . $db->escape($this->sourceName) .
                     "', `sourceURL`='" . $db->escape($this->sourceURL) .
                     "', `visible`='" . intval($this->visible) .
                     "', `important`='" . intval($this->important) . "' WHERE `id`='" . intval($this->id) . "'";
        }

        $db->query($query);
        if (is_null($this->id)) {
            $this->id = $db->getLastId();
        }

        $this->saveHTMLTexts();
    }

    /**
     * Сохраняет картинки и записывает в базу тексты анонса и тела новости
     */
    private function saveHTMLTexts()
    {

        if ($this->body instanceof \Difra\Param\AjaxHTML || $this->body instanceof \Difra\Param\AjaxSafeHTML) {
            $this->body->saveImages(DIR_DATA . 'news/body/' . $this->id, '/news/img/' . $this->id);
            $this->body = $this->body->val();
        }
        if ($this->announcement instanceof \Difra\Param\AjaxHTML ||
            $this->announcement instanceof \Difra\Param\AjaxSafeHTML
        ) {
            $this->announcement->saveImages(DIR_DATA . 'news/announcement/' . $this->id, '/news/a/img/' . $this->id);
            $this->announcement = $this->announcement->val();
        }

        $db = \Difra\MySQL::getInstance();
        $query = "UPDATE `news` SET `body`='" . $db->escape($this->body) . "', `announcement`='" .
                 $db->escape($this->announcement) .
                 "' WHERE `id`='" . intval($this->id) . "'";

        $db->query($query);
    }

    /**
     * Устанавливает все данные объекта
     * @param $data
     */
    private function _setObject($data)
    {

        if (!empty($data)) {
            $this->id = $data['id'];
            $this->title = $data['title'];
            $this->link = $data['link'];
            $this->pubDate = $data['pubDate'];
            $this->viewDate = $data['viewDate'];
            $this->stopDate = $data['stopDate'];
            $this->visible = $data['visible'];
            $this->important = $data['important'];
            $this->body = $data['body'];
            $this->announcement = $data['announcement'];
            $this->sourceName = $data['source'];
            $this->sourceURL = $data['sourceURL'];
            $this->modifiedDate = $data['modified'];
        }
    }

    /**
     * Возрвщает массив объектов с новостями
     * @static
     * @param bool $onlyVisible
     */
    public static function getList($page = null, $onlyVisible = false)
    {

        $db = \Difra\MySQL::getInstance();

        $where = '';
        $limits = '';
        if ($onlyVisible) {
            $where =
                " WHERE `visible`=1 AND `viewDate`<=NOW() AND (( NOT(`stopDate`='0000-00-00 00:00:00') AND `stopDate`>=NOW() ) " .
                "OR `stopDate`='0000-00-00 00:00:00') ";
        }

        if (!is_null($page)) {
            $perPage = \Difra\Config::getInstance()->getValue('news_settings', 'perPage');
            $limits = " LIMIT " . intval(($page - 1) * $perPage) . "," . intval($perPage);
        }

        $query = "SELECT * FROM `news` " . $where . "ORDER BY `important` DESC, `pubDate` DESC " . $limits;
        $res = $db->fetch($query);

        $objectsArray = null;
        if (!empty($res)) {
            foreach ($res as $k => $data) {
                $objectsArray[$data['id']] = new self;
                $objectsArray[$data['id']]->_setObject($data);
            }
        }
        return $objectsArray;
    }

    /**
     * Возвращает полную ссылку на новость
     */
    public function getTrueLink()
    {

        return 'http://' . \Difra\Site::getInstance()->getHostname() . '/news/' . $this->id . '-' . $this->link;
    }

    /**
     * Возвращает XML новости
     * @param \DOMNode $node
     */
    public function getXML($node, $withoutText = false)
    {

        $Locale = \Difra\Locales::getInstance();
        //getDateFromMysql

        $newsNode = $node->appendChild($node->ownerDocument->createElement('publication'));
        $newsNode->setAttribute('id', $this->id);
        $newsNode->setAttribute('title', $this->title);
        $newsNode->setAttribute('link', $this->link);
        $newsNode->setAttribute('trueLink', $this->getTrueLink());

        $newsNode->setAttribute('pubDate', $Locale->getDateFromMysql($this->pubDate));
        $newsNode->setAttribute('viewDate', $Locale->getDateFromMysql($this->viewDate));
        if (!is_null($this->stopDate) && $this->stopDate != '0000-00-00 00:00:00') {
            $newsNode->setAttribute('stopDate', $Locale->getDateFromMysql($this->stopDate));
        }

        $newsNode->setAttribute('visible', $this->visible);
        $newsNode->setAttribute('important', $this->important);

        if (!$withoutText) {
            $newsNode->setAttribute('body', $this->body);
            $newsNode->setAttribute('announcement', $this->announcement);
        }

        if (!is_null($this->sourceName) && $this->sourceName != '') {
            $newsNode->setAttribute('sourceName', $this->sourceName);
        }
        if (!is_null($this->sourceURL) && $this->sourceURL != '') {
            $newsNode->setAttribute('sourceURL', $this->sourceURL);
        }
    }

    /**
     * Загружает объект новости по её id
     * @static
     * @param int $id
     */
    public static function getById($id)
    {

        $db = \Difra\MySQL::getInstance();
        $query = "SELECT * FROM `news` WHERE `id`='" . intval($id) . "'";
        $res = $db->fetchRow($query);
        if (!empty($res)) {
            $Pub = new self;
            $Pub->_setObject($res);
            return $Pub;
        }
        return null;
    }

    /**
     * Удаляет новость
     * @static
     * @param $id
     */
    public static function delete($id)
    {

        $db = \Difra\MySQL::getInstance();
        $db->query("DELETE FROM `news` WHERE `id`='" . intval($id) . "'");

        $path = DIR_DATA . 'news/body/' . intval($id);
        if (is_dir($path)) {
            $dir = opendir($path);
            while (false !== ($file = readdir($dir))) {
                if ($file{0} == '.') {
                    continue;
                }
                @unlink("$path/$file");
            }
            rmdir($path);
        }

        $path = DIR_DATA . 'news/announcement/' . intval($id);
        if (is_dir($path)) {
            $dir = opendir($path);
            while (false !== ($file = readdir($dir))) {
                if ($file{0} == '.') {
                    continue;
                }
                @unlink("$path/$file");
            }
            rmdir($path);
        }
    }

    /**
     * Возвращает объект по ссылке
     * @static
     * @param string $link
     */
    public static function getByLink($link)
    {

        $link = explode('-', $link, 2);
        if (sizeof($link) < 2 || !is_numeric($link[0])) {
            return null;
        }
        $db = \Difra\MySQL::getInstance();
        $query =
            "SELECT * FROM `news` WHERE `id`='" . intval($link[0]) . "' AND `link`='" . $db->escape($link[1]) . "'";
        $res = $db->fetchRow($query);
        if (!empty($res)) {

            $Pub = new self;
            $Pub->_setObject($res);
            return $Pub;
        }
        return null;
    }

    public function __destruct()
    {

        if ($this->modified && $this->loaded) {
            $this->save();
        }
    }
}
