<?php

namespace Difra\Plugins\Blogs;

use Difra;
use Difra\Auth;
use Difra\MySQL;
use Difra\Param;

class Post
{
    private $id = null;
    private $user = null;
    private $nickname = null;
    private $groupName = null;
    private $groupDomain = null;
    private $blog = null;
    private $title = null;
    private $link = null;
    private $preview = null;
    private $text = null;
    private $date = null;
    private $visible = null;
    private $comments = 0;
    private $canModify = false;
    private $groupId = false;

    /**
     * Создаёт новый пост.
     * Массив $data может содержать следующие ключи:
     * title        Заголовок поста
     * text                Тело поста
     * visible        1 = показывать пост, 0 = черновик
     * @param int $blogId
     * @param int $userId
     * @param array $data
     * @return Post
     */
    public static function add($blogId, $userId, $data = [])
    {
        $post = new self;
        $post->blog = intval($blogId);
        $post->user = intval($userId);
        $keys = ['title', 'text', 'visible'];
        foreach ($keys as $key) {
            if ($data[$key] != '') {
                $post->$key = $data[$key];
            }
        }
        $post->makeLink();
        $post->makePreview();
        $post->save();
        return $post;
    }

    /**
     * Создаёт пост (или несколько) из результатов выборки из базы
     * @static
     * @param array $data
     * @return array|null
     */
    public static function makeList($data)
    {
        // нет данных
        if (empty($data) or !is_array($data)) {
            return null;
        }
        // в $data один пост
        if (isset($data['id'])) {
            $data = [$data];
        }

        $groups = [];
        $Auth = Auth::getInstance();
        if ($userId = $Auth->getEmail()) {
            $groups = Group::getOwnedGroupsIds($userId);
        }

        $posts = [];
        foreach ($data as $row) {
            $post = new self;
            foreach ($row as $k => $v) {
                if (property_exists($post, $k)) {
                    $post->$k = $v;
                }
            }
            if ($userId and ($post->getUser() == $userId || $Auth->isModerator())) {
                $post->canModify = true;
            } elseif ($userId and $post->groupId and in_array($post->groupId, $groups)) {
                $post->canModify = true;
            } else {
                $post->canModify = false;
            }
            $posts[] = $post;
        }
        return $posts;
    }

    /**
     * Возвращает объект POST по id
     * @static
     * @param $id
     * @return bool|Post
     */
    public static function getById($id)
    {
        $db = MySQL::getInstance();
        $data = $db->fetchRow("SELECT * FROM `blogs_posts` WHERE `id`='" . $db->escape($id) . "'");
        if (!$data) {
            return false;
        }
        $post = new self;
        foreach ($data as $k => $v) {
            if (property_exists($post, $k)) {
                $post->$k = $v;
            }
        }
        return $post;
    }

    public function getBlog()
    {
        return Blog::getById($this->blog);
    }

    /**
     * Сохраняет пост в базу
     */
    public function save()
    {
        $vars = get_object_vars($this);
        $sets = [];
        $db = MySQL::getInstance();
        foreach ($vars as $name => $value) {
            if (!is_null($value)) {
                switch ($name) {
                    case 'comments':
                    case 'nickname':
                    case 'canModify':
                    case 'groupId':
                        break;
                    default:
                        $sets[] = "`" . $db->escape($name) . "`='" . $db->escape($value) . "'";
                }
            }
        }
        $db->query("REPLACE INTO `blogs_posts` SET " . implode(',', $sets));
        $this->id = $db->getLastId();
    }

    /**
     * @param \DOMElement $node
     * @param bool $nosubNode
     */
    public function getXML($node, $nosubNode = false)
    {
        if ($nosubNode) {
            $postNode = $node;
        } else {
            $postNode = $node->appendChild($node->ownerDocument->createElement('post'));
        }
        $postNode->setAttribute('id', $this->id);
        $postNode->setAttribute('user', $this->user);
        $postNode->setAttribute('nickname', $this->nickname);
        $postNode->setAttribute('groupName', $this->groupName);
        $postNode->setAttribute('groupDomain', $this->groupDomain);
        $postNode->setAttribute('blog', $this->blog);
        $postNode->setAttribute('title', $this->title);
        $postNode->setAttribute('link', $this->getLink());
        $postNode->setAttribute('url', $this->getUrl());
        $postNode->setAttribute('preview', $this->preview);
        $postNode->setAttribute('date', $this->date);
        //$postNode->setAttribute( 'date', \Difra\Locales::getInstance()->getDateFromMysql( $this->date, true ) );
        $postNode->setAttribute('visible', $this->visible);
        $postNode->setAttribute('comments', $this->comments);
        $postNode->setAttribute('text', $this->text);
        $postNode->setAttribute('canModify', $this->canModify ? '1' : '0');
    }

    public function setTitle($newTitle)
    {
        $this->title = trim($newTitle);
        $this->makeLink();
    }

    public function setText($newText)
    {
        $this->text = trim($newText);
        $this->makePreview();
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function makePreview()
    {
        $parts = str_replace('</p>', '', $this->text);
        $parts = explode('<p>', $parts);
        $p = $n = 0;
        $preview = '';
        // получаем до 900 символов или до 3 параграфов
        while (isset($parts[$p]) and (mb_strlen($preview) + mb_strlen($parts[$p]) < 900) and $n < 3) {
            $parts[$p] = preg_replace("/(^\s+)|(\s+$)/us", '', $parts[$p]);
            if (strlen($parts[$p])) {
                $preview .= '<p>' . $parts[$p] . '</p>';
                $n++;
            }
            ++$p;
        }
        // если получилось меньше 500 символов, но есть ещё параграф — распилим его
        if ($n <= 3 and mb_strlen($preview) < 500 and isset($parts[$p])) {
            // отрезаем сколько нужно
            $chunk = preg_replace("/(^\s+)|(\s+$)/us", '', $parts[$p]);
            $chunk = mb_substr($chunk, 0, 960 - mb_strlen($preview));
            // обрезали посреди тэга?
            if (mb_strrpos($chunk, '<') > mb_strrpos($chunk, '>')) {
                $chunk = mb_substr($chunk, 0, mb_strrpos($chunk, '<'));
            }
            // обрезаем по конец предложения
            $cut = 0;
            $fins = ['.', '!', '?', '…'];
            foreach ($fins as $fin) {
                if (($t = mb_strrpos($chunk, $fin)) > $cut) {
                    $cut = $t;
                }
            }
            // но не менее 200 символов
            if ($cut and $cut > 200) {
                $chunk = mb_substr($chunk, 0, $cut);
                $preview .= '<p>' . $chunk . '</p>';
            } elseif ($cut = mb_strrpos($chunk, ' ') > 200) {
                $chunk = mb_substr($chunk, 0, $cut);
                $preview .= '<p>' . $chunk . '</p>';
            }
        }
        $this->preview = Param\Filters\HTML::getInstance()->process($preview, false, false);
    }

    public function makeLink()
    {
        $link = '';
        $num = preg_match_all('/[A-Za-zА-Яа-я0-9Ёё]*/u', $this->title, $matches);
        if ($num and !empty($matches[0])) {
            $matches = array_filter($matches[0], 'strlen');
            $link = implode('-', $matches);
        }
        if ($link == '') {
            $link = '-';
        }
        $this->link = $link;
    }

    public function getLink()
    {
        if (!$this->link) {
            $this->makeLink();
        }
        return $this->link;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUrl()
    {
        if (!$this->groupDomain) {
            return 'http://' . \Difra\Envi::getHost(true) . '/blogs/' . rawurlencode($this->nickname) .
                   '/' . $this->id . '/' . $this->getLink();
        } elseif ($this->blog != 1) {
            return 'http://' . $this->groupDomain . '.' . \Difra\Envi::getHost(true) . '/' . $this->id .
                   '/' . $this->getLink();
        } else {
            return 'http://' . \Difra\Envi::getHost(true) . '/' . $this->id . '/' . $this->getLink();
        }
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getBlogId()
    {
        return $this->blog;
    }

    public function setUser($newUser)
    {
        $this->user = intval($newUser);
    }

    public static function delete($id)
    {
        $db = MySQL::getInstance();
        $db->query("DELETE FROM `blogs_posts` WHERE `id`='" . intval($id) . "'");

        if (is_dir(DIR_HTDOCS . '/blogs/images/' . intval($id))) {
            $objects = scandir(DIR_HTDOCS . '/blogs/images/' . intval($id));
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype(DIR_HTDOCS . '/blogs/images/' . intval($id) . "/" . $object) == "dir") {
                        rmdir(DIR_HTDOCS . '/blogs/images/' . intval($id) . "/" . $object);
                    } else {
                        unlink(DIR_HTDOCS . '/blogs/images/' . intval($id) . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir(DIR_HTDOCS . '/blogs/images/' . intval($id));
        }
    }
}
