<?php

namespace Difra\Plugins\Blogs;

use Difra;

class Blog
{
	const        POSTS_PER_PAGE = 10;
	/** @var int */
	private $id = null;
	/** @var string */
	private $name = '';
	private $user = null;
	private $group = null;
	/** @var array */
	private $tagsItems = null;

	public static function load($id)
	{
	}

	/**
	 * Возвращает объект Blog по строчке из базы
	 * @param array $data
	 * @return Blog
	 */

	private static function _makeInstance($data)
	{

		// no data
		if (empty($data)) {
			return null;
		}

		$blog = new self;
		$vars = ['id', 'name', 'user', 'group'];
		foreach ($data as $k => $v) {
			if (in_array($k, $vars)) {
				$blog->$k = $v;
			}
		}
		return $blog;
	}

	/**
	 * Возвращает объект Blog по id пользователя
	 * @param int $userId
	 * @return Blog
	 */
	public static function getByUser($userId)
	{

		$db = Difra\MySQL::getInstance();
		$data = $db->fetchRow("SELECT * FROM `blogs` WHERE `user`='" . $db->escape($userId) . "'");
		return self::_makeInstance($data);
	}

	/**
	 * Возвращает объект Blog по id блога
	 * @param int $id
	 * @return Blog
	 */
	public static function getById($id)
	{

		$db = Difra\MySQL::getInstance();
		$data = $db->fetch("SELECT * FROM `blogs` WHERE `id`='" . intval($id) . "'");
		return self::_makeInstance($data);
	}

	public static function getAll()
	{

		$blog = new self;
		$blog->id = false;
		return $blog;
	}

	public static function getAllWithTags($tagsItemsId)
	{

		$blog = new self;
		$blog->id = false;
		$blog->tagsItems = $tagsItemsId;
		return $blog;
	}

	/**
	 * Возвращает объект Blog по id группы
	 * @param int $groupId
	 * @return Blog
	 */
	public static function getByGroup($groupId)
	{

		$db = Difra\MySQL::getInstance();
		$data = $db->fetchRow("SELECT * FROM `blogs` WHERE `group`='" . $db->escape($groupId) . "'");
		return self::_makeInstance($data);
	}

	/**
	 * Возвращает Blog по id пользователя. Если блога ещё нет, он будет создан.
	 * @param int $userId
	 * @return Blog
	 */
	public static function touchByUser($userId)
	{

		if ($res = self::getByUser($userId)) {
			return $res;
		}
		$blog = new self;
		$blog->user = intval($userId);
		$blog->_save();
		return $blog;
	}

	/**
	 * Возвращает Blog по id группы. Если блога ещё нет, он будет создан.
	 * @param int $groupId
	 * @return Blog
	 */
	public static function touchByGroup($groupId)
	{

		if ($res = self::getByGroup($groupId)) {
			return $res;
		}
		$blog = new self;
		$blog->group = $groupId;
		$blog->_save();
		return self::getByGroup($groupId);
	}

	/**
	 * Сохраняет в базу
	 */
	private function _save()
	{

		if (!$this->user and !$this->group) {
			throw new \Difra\Exception('Attempt to save blog with empty `user` and `group` fields');
		}
		$keys = ['name']; // свойства, которые нужно сохранять
		$sets = [];
		$db = Difra\MySQL::getInstance();
		foreach ($keys as $key) {
			if (!is_null($this->$key)) {
				$sets[] = "`$key`='" . $db->escape($this->$key) . "'";
			}
		}
		// ни в коем случае нельзя давать id,user,group в одной строке REPLACE
		if ($this->id) {
			$sets[] = "`id`='{$this->id}'";
		} elseif ($this->group) {
			$sets[] = "`group`='{$this->group}'";
		} elseif ($this->user) {
			$sets[] = "`user`='{$this->user}'";
		} else {
			throw new Difra\Exception('Attempt to save blog without id, user, group');
		}
		$db->query("REPLACE INTO `blogs` SET " . implode(",", $sets));
	}

	/**
	 * Получает последние записи из блога
	 * @param int $page страница
	 * @param int $perPage количество записей на страницу
	 * @param bool $hidden показывать скрытые записи вместо не скрытых
	 * @param bool $noNickName убирать ли из условия никнейм пользователей
	 * @return array        массив: 'posts' => массив постов, 'total' => количество записей, 'pages' => количество
	 *     страниц
	 */
	public function getPosts($page = 1, $perPage = null, $hidden = false, $noNickName = false)
	{

		if (!$perPage) {
			$perPage = self::POSTS_PER_PAGE;
		}
		if (!$page) {
			$page = 1;
		}
		$perPage = intval($perPage);
		$first = ($page - 1) * $perPage;

		if (!is_null($hidden)) {
			$visibleCond = ' AND `visible`=\'' . ($hidden ? '0' : '1') . "' ";
			$key = 'key_blog_visible_date';
		} else {
			$visibleCond = '';
			$key = 'key_blog_date ';
		}

		$db = Difra\MySQL::getInstance();

		$cond = [];
		if (is_array($this->id)) {
			$cond[] = "`blog` IN ('" . implode("','", $this->id) . "')";
		} elseif ($this->id) {
			$cond[] = "`blog`='{$this->id}'";
		}
		// забэкапено
		//		$cond[] = "`users_fields`.`name`='nickname'";

		if (!$noNickName) {
			$cond[] = "`users_fields`.`name`='nickname'";
		}

		if (!is_null($hidden)) {
			$cond[] = "`visible`='" . ($hidden ? '0' : '1') . "'";
			$key = 'key_blog_visible_date';
		} else {
			$key = 'key_blog_date ';
		}
		// итемсы тегов
		if (is_array($this->tagsItems)) {
			$tagsLimit = " AND `blogs_posts`.`id` IN (" . implode(', ', $this->tagsItems) . ")";
		} else {
			$tagsLimit = '';
		}

		$query1 =
			' FROM `blogs_posts` LEFT JOIN `users_fields` ON `blogs_posts`.`user`=`users_fields`.`id` WHERE ' .
			implode(' AND ', $cond)
			. $tagsLimit;
		$query =
			'SELECT `t1`.*,`groups`.`name` AS `groupName`,`groups`.`domain` AS `groupDomain`, `groups`.`id` AS `groupId` FROM (SELECT `blogs_posts`.`id`,`blog`,`user`,`title`,`link`,`preview`,`date`,`visible`,`users_fields`.`value` as `nickname` '
			. $query1
			.
			" ORDER BY `date` DESC LIMIT {$first},{$perPage}) AS `t1` LEFT JOIN `blogs` USE KEY(`id_group`) ON `t1`.`blog`=`blogs`.`id` LEFT JOIN `groups` ON `blogs`.`group`=`groups`.`id`";

		$data = $db->fetchWithId($query);
		$total = $db->fetchOne('SELECT COUNT(`blogs_posts`.`id`) ' . $query1);

		// считаем комментарии
		if (!empty($data)) {
			$comments =
				$db->fetch('SELECT `parent_id` AS `id`,count(`parent_id`) AS `count` FROM `blogs_comments` WHERE `parent_id` IN ('
						   . implode(',', array_keys($data))
						   . ') GROUP BY `parent_id`');
			if (!empty($comments)) {
				foreach ($comments as $comment) {
					$data[$comment['id']]['comments'] = $comment['count'];
				}
			}
		}

		return [
			'posts' => Post::makeList($data),
			'first' => $first,
			'last' => $first + $perPage,
			'total' => $total,
			'pages' => (($total - 1) / $perPage) + 1
		];
	}

	/**
	 * @desc Возвращает Post по id
	 * @param $id
	 * @return Post
	 */
	public function getPost($id)
	{

		$db = Difra\MySQL::getInstance();
		$conditions = [];
		if (is_array($this->id)) {
			$conditions[] = "`blog` IN ('" . implode("','", $this->id) . "')";
		} elseif ($this->id) {
			$conditions[] = "`blog`='{$this->id}'";
		}
		$conditions[] = "`blogs_posts`.`id`='" . $db->escape($id) . "'";
		$conditions[] = "`users_fields`.`name`='nickname'";
		$conditions[] = "`visible`='1'";

		$query = 'SELECT `blogs_posts`.`id`,`blog`,`user`,`title`,`link`,`preview`,`date`,`visible`,`text`,' .
				 '`users_fields`.`value` as `nickname` '
				 . 'FROM `blogs_posts` LEFT JOIN `users_fields` ON `blogs_posts`.`user`=`users_fields`.`id` '
				 . 'WHERE ' . implode(' AND ', $conditions);

		$query = 'SELECT `t1` .*,`groups`.`name` AS `groupName`,`groups`.`domain` AS `groupDomain` '
				 . "FROM($query) AS `t1` LEFT JOIN `blogs` USE KEY( `id_group` ) "
				 . 'ON `t1`.`blog`=`blogs`.`id` LEFT JOIN `groups` ON `blogs`.`group`=`groups`.`id`';

		if (!$data = $db->fetchWithId($query)) {
			return null;
		}
		$post = Post::makeList($data);
		if (sizeof($post) != 1) {
			return null;
		}

		return array_shift($post);
	}

	/**
	 * @param \DOMElement $node
	 * @param int $page
	 * @param int $perPage
	 * @param int $hidden
	 * @param bool $noNickName
	 */
	public function getPostsXML($node, $page = 1, $perPage = null, $hidden = 0, $noNickName = false)
	{

		//TODO на избранных блогах получается, что id приходит в виде массива. Поставил @ что бы не ругалось, на работу функции это не сказывается.
		@$node->setAttribute('id', $this->id);
		$node->setAttribute('user', $this->user);
		$node->setAttribute('group', $this->group);
		$node->setAttribute('name', $this->name);

		$posts = $this->getPosts($page, $perPage, $hidden, $noNickName);
		$node->setAttribute('current', $page);
		$node->setAttribute('first', $posts['first']);
		$node->setAttribute('last', $posts['last']);
		$node->setAttribute('total', $posts['total']);
		$node->setAttribute('pages', floor($posts['pages']));
		if (empty($posts['posts'])) {
			$node->appendChild($node->ownerDocument->createElement('empty'));
			return;
		}
		foreach ($posts['posts'] as $post) {
			$post->getXML($node);
		}
	}

	/**
	 * Добавляет пост
	 * @param int $userId
	 * @param string $title
	 * @param string $text
	 * @param bool $hidden
	 * @return Post
	 */
	public function addPost($userId, $title, $text, $hidden = false)
	{

		return Post::add($this->id, $userId, [
			'title' => $title,
			'text' => $text,
			'visible' => $hidden ? '0' : '1'
		]);
	}

	public static function addFriend($user, $blog)
	{

		$db = \Difra\MySQL::getInstance();
		$db->query("INSERT IGNORE INTO `blogs_friends` (`user`,`blog`) VALUES ('" . $db->escape($user) . "','"
				   . $db->escape($blog) . "')");
	}

	public static function delFriend($user, $blog)
	{

		$db = \Difra\MySQL::getInstance();
		$db->query("DELETE FROM `blogs_friends` WHERE `user`='" . $db->escape($user) . "' AND `blog`='"
				   . $db->escape($blog) . "'");
	}

	public static function getFriends($user)
	{

		$db = \Difra\MySQL::getInstance();
		$ids = $db->fetch("SELECT `blog` FROM `blogs_friends` WHERE `user`='" . $db->escape($user) . "'");
		if (empty($ids)) {
			return false;
		}
		$blog = new Blog();
		$blog->id = [];
		foreach ($ids as $rec) {
			$blog->id[] = $rec['blog'];
		}
		return $blog;
	}

	public static function getFriendsPreviewXML($user, $node)
	{

		$db = \Difra\MySQL::getInstance();
		$db->fetchXML($node, "SELECT bf.`blog`, b.`user`, b.`group`, g.`name` AS `groupName`, g.`domain`, uf.`value` AS `nickname`
						FROM `blogs_friends` bf
						RIGHT JOIN `blogs` AS `b` ON b.`id`=bf.`blog`
						LEFT JOIN `groups` AS `g` ON g.`id`=b.`group`
						LEFT JOIN `users_fields` AS `uf` ON uf.`id`=b.`user` AND uf.`name`='nickname'
						WHERE bf.`user` = '" . intval($user) . "'");
	}

	/**
	 * @return int
	 */
	public function getId()
	{

		return $this->id;
	}

	public function getGroup()
	{

		return Group::getById($this->group);
	}

	public function getGroupId()
	{

		return $this->group;
	}
}
