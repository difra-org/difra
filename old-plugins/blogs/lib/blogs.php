<?php

namespace Difra\Plugins;

class Blogs
{
	public static function getInstance()
	{

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function __construct()
	{
	}

	// Группы

	public function isGroupNameAvailable($name)
	{

		$name = trim($name);
		if (!$name) {
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		$id = $db->fetchOne("SELECT `id` FROM `groups` WHERE `name`='" . $db->escape(trim($name)) . "'");
		return empty($id);
	}

	public function isGroupDomainAvailable($domain)
	{

		$domain = trim($domain);
		if (!$domain) {
			return false;
		}
		$db = \Difra\MySQL::getInstance();
		$id = $db->fetchOne("SELECT `id` FROM `groups` WHERE `domain`='" . $db->escape(trim($domain)) . "'");
		return empty($id);
	}

	const NAME_BUSY = 'name_busy';
	const NO_ACCESS = 'no_access';

	public function setGroupName($id, $name)
	{

		$auth = \Difra\Auth::getInstance();
		if (!$auth->logged) {
			throw new \Difra\Exception('Unauthorized users can\'t change groups');
		}
		if (!$this->isGroupNameAvailable($name)) {
			return self::NAME_BUSY;
		}
		$group = Blogs\Group::load($id);
		if (!$group->isOwner($userId = $auth->getId())) {
			throw new \Difra\Exception("User {$userId} is forbidden to rename group $id");
		}
		$group->setName($name);
		return false;
	}

	const DOMAIN_BUSY = 'domain_busy';

	public function setGroupDomain($id, $domain)
	{

		$auth = \Difra\Auth::getInstance();
		if (!$auth->logged) {
			throw new \Difra\Exception('Unauthorized users can\'t change groups');
		}
		if (!$this->isGroupDomainAvailable($domain)) {
			return self::DOMAIN_BUSY;
		}
		$group = Blogs\Group::load($id);
		if (!$group->isOwner($userId = $auth->getId())) {
			throw new \Difra\Exception("User {$userId} is forbidden to change domain for group {$id}");
		}
		$group->setDomain($domain);
		return false;
	}

	/**
	 * Добавление текущего пользователя в группу
	 * Массив $data описан в функции Group::addUser()
	 * @param int $groupId
	 * @param array $data
	 * @throws \Difra\Exception
	 * @return bool
	 */
	public function addGroupUser($groupId, $data = [])
	{

		$auth = \Difra\Auth::getInstance();
		$auth->required();
		$userId = $auth->getId();

		if (!$group = Blogs\Group::load($groupId)) {
			throw new \Difra\Exception("Can't add user to non-existent group");
		}

		if ($group->getOwner() == $userId) {
			return;
		}

		$group->addUser($userId, $data);
	}

	/**
	 * Удаление заданного пользователя из заданной группы
	 * (доступно только владельцам групп)
	 * @param int $groupId
	 * @param int $userId
	 * @throws \Difra\Exception
	 */
	public function delGroupUser($groupId, $userId)
	{

		$auth = \Difra\Auth::getInstance();
		$auth->required();
		$user = $auth->getId();

		if (!$group = Blogs\Group::load($groupId)) {
			throw new \Difra\Exception("Can't add user to non-existent group");
		}
		if (!$group->isOwner($user)) {
			throw new \Difra\Exception("User {$user} is forbidden to delete users from group {$groupId}");
		}

		$group->delUser($userId);
	}

	/**
	 * Устанавливает нового владельца группы
	 * @param int $groupId
	 * @param int $userId
	 * @throws \Difra\Exception
	 */
	public function setGroupOwner($groupId, $userId)
	{

		$auth = \Difra\Auth::getInstance();
		$auth->required();
		$user = $auth->getId();

		if (!$group = Blogs\Group::load($groupId)) {
			throw new \Difra\Exception("Can't add user to non-existent group");
		}
		if (!$group->isOwner($user)) {
			throw new \Difra\Exception("User {$user} is forbidden to set ower of group {$groupId}");
		}
		if (!$group->hasUser($userId)) {
			throw new \Difra\Exception("User {$userId} is not in group {$groupId}");
		}

		$group->setOwner($userId);
	}

	/**
	 * Устанавливает роль заданного пользователя в группе
	 * @param int $groupId
	 * @param string $role
	 * @throws \Difra\Exception
	 */
	public function setGroupRole($groupId, $role)
	{

		$auth = \Difra\Auth::getInstance();
		$auth->required();
		$user = $auth->getId();

		if (!$group = Blogs\Group::load($groupId)) {
			throw new \Difra\Exception("Can't modify user in non-existent group");
		}
		if (!$group->isOwner($user)) {
			throw new \Difra\Exception("User {$user} is forbidden set comments in group {$groupId}");
		}
		if (!$group->hasUser($user)) {
			throw new \Difra\Exception("User {$user} is not in group {$groupId}");
		}

		$group->setUserRole($user, $role);
	}

	/**
	 * Принимает пользователя в группу
	 * @param int $groupId
	 * @param int $userId
	 * @throws \Difra\Exception
	 */
	public function confirmGroupUser($groupId, $userId)
	{

		$auth = \Difra\Auth::getInstance();
		$auth->required();
		$user = $auth->getId();

		if (!$group = Blogs\Group::load($groupId)) {
			throw new \Difra\Exception("Can't modify user in non-existent group");
		}
		if (!$group->isOwner($user)) {
			throw new \Difra\Exception("User {$user} is forbidden to confirm users in group {$groupId}");
		}
		if (!$group->hasUser($userId)) {
			throw new \Difra\Exception("User {$userId} is not in group {$groupId}");
		}

		$group->setUserConfirmed($userId);
	}

	/**
	 * Заполняет DOM-узел содержимым блога пользователя
	 * @param \DOMNode $node XML-узел
	 * @param int $userId id пользователя
	 * @param int $page номер страницы
	 * @param int $perPage количество постов на страницу
	 * @param bool $noNickName
	 * @return int                ID блога
	 */
	public function getUserBlogXML($node, $userId, $page = 1, $perPage = null, $noNickName = false)
	{

		$blog = Blogs\Blog::touchByUser($userId);
		$blog->getPostsXML($node, $page, $perPage, 0, $noNickName);
		return $blog->getId();
	}

	public function getGroupBlogXML($node, $groupId, $page = 1, $perPage = null, $noNickName = false)
	{

		$blog = Blogs\Blog::touchByGroup($groupId);
		$blog->getPostsXML($node, $page, $perPage, 0, $noNickName);
		return $blog;
	}

	public function getAllPostsXML($node, $page = 1, $tagsItems = null, $perPage = null, $noNickName = false)
	{

		if (is_array($tagsItems)) {
			$blog = Blogs\Blog::getAllWithTags($tagsItems);
		} else {
			$blog = Blogs\Blog::getAll();
		}
		$blog->getPostsXML($node, $page, $perPage, 0, $noNickName);
	}

	/**
	 * @param \DOMElement $node
	 */
	public function getUserGroupsXML($node)
	{

		$auth = \Difra\Auth::getInstance();
		if (!$auth->logged) {
			return;
		}
		$groupsNode = $node->appendChild($node->ownerDocument->createElement('groups'));
		Blogs\Group::getGroupsByUserXML($groupsNode, $auth->getId());
	}

	public function getPost($userId, $postId)
	{

		if (!$blog = Blogs\Blog::getByUser($userId)) {
			return false;
		}
		return $blog->getPost($postId);
	}

	public function getGroupPost($groupId, $postId)
	{

		if (!$blog = Blogs\Blog::getByGroup($groupId)) {
			return false;
		}
		return $blog->getPost($postId);
	}

	public function addFriend($blogId)
	{

		$auth = \Difra\Auth::getInstance();
		$auth->required();
		Blogs\Blog::addFriend($auth->getId(), $blogId);
	}

	public function delFriend($blogId)
	{

		$auth = \Difra\Auth::getInstance();
		$auth->required();
		Blogs\Blog::delFriend($auth->getId(), $blogId);
	}

	/**
	 * @param \DOMElement $node
	 * @param bool $userId
	 * @param int $page
	 * @param null $perPage
	 */
	public function getFriendsXML($node, $userId = false, $page = 1, $perPage = null)
	{

		if (!$userId) {
			$userId = \Difra\Auth::getInstance()->getId();
		}
		if ($userId) {
			$friends = Blogs\Blog::getFriends($userId);
			if (empty($friends)) {
				$node->appendChild($node->ownerDocument->createElement('empty'));
				return;
			}
			$friends->getPostsXML($node, $page, $perPage);
		}
	}

	/**
	 * Делает +1 для статистики поста
	 * @param      $postId
	 * @param null $groupId
	 * @param null $userId
	 * @return bool
	 */
	public function savePostStat($postId, $groupId = null, $userId = null)
	{

		$Cache = \Difra\Cache::getInstance();
		$db = \Difra\MySQL::getInstance();

		if (!$db->fetchRow("SHOW TABLES LIKE 'blogs_stat'")) {
			return false;
		}

		$postsStat = $Cache->get('posts_stat');

		$client = \Difra\Auth::getInstance()->getId();
		if (is_null($client)) {
			$client = $_SERVER['REMOTE_ADDR'];
		}

		$groupAdd = $userAdd = '';
		if (!is_null($groupId)) {
			$groupAdd = ", `group_id`='" . intval($groupId) . "' ";
		}
		if (!is_null($userId)) {
			$userAdd = ", `user_id`='" . intval($userId) . "' ";
		}

		$query = "INSERT INTO `blogs_stat` SET `date`='" . date('Y-m-d', time()) . "', `post_id`='" . intval($postId) .
				 "', `count`=1 " . $groupAdd . $userAdd . " ON DUPLICATE KEY UPDATE `count`=`count`+1";

		if (isset($postsStat[$client])) {
			if (!in_array($postId, $postsStat[$client])) {

				if (count($postsStat[$client]) == 3) {
					array_shift($postsStat[$client]);
				}
				$postsStat[$client][] = $postId;
				$Cache->put('posts_stat', $postsStat);
				$db->query($query);
				return true;
			} else {
				return false;
			}
		}
		$postsStat[$client][] = $postId;
		$Cache->put('posts_stat', $postsStat);
		$db->query($query);
		return true;
	}
}

