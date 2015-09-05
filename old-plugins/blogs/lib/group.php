<?php

namespace Difra\Plugins\Blogs;

use Difra;

class Group
{
	private $id = 0;
	private $name = '';
	private $domain = '';
	private $owner = null;
	private $users = null;

	/**
	 * Создаёт новую группу.
	 * Массив $data может содержать следующие ключи:
	 * owner        id владельца (обязательно)
	 * name                название группы
	 * domain        имя поддомена группы
	 * Поддерживается Additionals
	 * @param array $data
	 * @throws \Difra\Exception
	 * @return Group
	 */
	public static function create($data)
	{

		$db = Difra\MySQL::getInstance();
		$instance = new self;

		$query = 'INSERT INTO `groups`';
		if (!empty($data['name']) or !trim($data['name'])) {
			$instance->name = $db->escape(trim($data['name']));
		}
		$query .= " SET `name`='{$instance->name}'";
		if (!empty($data['domain']) or !trim($data['domain'])) {
			$instance->domain = $db->escape(trim($data['domain']));
		}
		$query .= ",`domain`='{$instance->domain}'";

		if (empty($data['owner']) or !$data['owner'] or !ctype_digit($data['owner']) or !intval($data['owner'])) {
			throw new Difra\Exception("Group::create() expects valid owner id");
		}
		$instance->owner = $db->escape($data['owner']);
		$query .= ",`owner`='{$instance->owner}'";

		if (true !== ($res = Difra\Additionals::checkAdditionals('groups', $data))) {
			return $res;
		}

		$db->query($query);
		if (!$instance->id = $db->getLastId()) {
			throw new \Difra\Exception("Group::create() failed to create a new group");
		}

		Difra\Additionals::saveAdditionals('groups', $instance->id, $data);

		return $instance;
	}

	/**
	 * Загружает группу по id
	 * @param int $id
	 * @return Group
	 */
	public static function load($id)
	{

		static $_instances = [];
		if (!isset($_instances[$id])) {
			$db = Difra\MySQL::getInstance();
			$groupData = $db->fetchRow("SELECT * FROM `groups` WHERE `id`='" . $db->escape($id) . "'");
			if (empty($groupData)) {
				return $_instances[$id] = null;
			}
			$_instances[$id] = self::makeGroup($groupData);
		}
		return $_instances[$id];
	}

	/**
	 * @static
	 * @param $userId
	 * @return self[]
	 */
	public static function getGroupsByUser($userId)
	{

		$db = Difra\MySQL::getInstance();
		$groupsData = $db->fetch('SELECT `groups`.* FROM `groups_users` USE KEY (`key_user_confirmed_group`) '
								 . 'LEFT JOIN `groups` ON `groups_users`.`group`=`groups`.`id` '
								 . "WHERE `user`='" . $db->escape($userId) . "' AND `confirmed`='1'");
		if (empty($groupsData)) {
			return null;
		}
		$groups = [];
		foreach ($groupsData as $data) {
			$groups[] = self::makeGroup($data);
		}
		return $groups;
	}

	public static function getOwnedGroupsIds($userId)
	{

		$db = \Difra\MySQL::getInstance();
		$data = $db->fetch("SELECT `id` FROM `groups` WHERE `owner`='" . $db->escape($userId) . "'");
		$res = [];
		if (!empty($data)) {
			foreach ($data as $row) {
				$res[] = $row['id'];
			}
		}
		return $res;
	}

	public static function getByDomain($domain)
	{

		$mainHost = Difra\Site::getInstance()->getMainhost();
		if ($mainHost == $domain) {
			return null;
		}
		if (substr($domain, strlen($domain) - strlen($mainHost)) != $mainHost) {
			return null;
		}
		$domain = substr($domain, 0, strlen($domain) - strlen($mainHost) - 1);
		$db = Difra\MySQL::getInstance();
		$groupData = $db->fetchRow('SELECT * FROM `groups` WHERE `domain`=\'' . $db->escape($domain) . "'");
		return self::makeGroup($groupData);
	}

	public static function getById($id)
	{

		$db = \Difra\MySQL::getInstance();
		$data = $db->fetchRow("SELECT * FROM `groups` WHERE `id`='" . $db->escape($id) . "'");
		return self::makeGroup($data);
	}

	private static function makeGroup($groupData)
	{

		if (!$groupData) {
			return null;
		}
		$group = new self;
		$group->id = $groupData['id'];
		$group->name = $groupData['name'];
		$group->domain = $groupData['domain'];
		$group->owner = $groupData['owner'];
		return $group;
	}

	public static function getGroupsByUserXML($node, $userId)
	{

		if (!$data = self::getGroupsByUser($userId)) {
			return null;
		}
		foreach ($data as $group) {
			$group->getXML($node);
		}
	}

	/**
	 * Проверяет, является ли пользователь владельцем группы
	 * @param int $id
	 * @return bool
	 */
	public function isOwner($id)
	{

		return ($this->owner == $id);
	}

	/**
	 * Устанавливает нового владельца группы
	 * Текущий владелец становится участником группы
	 * @param int $id
	 */
	public function setOwner($id)
	{

		$db = Difra\MySQL::getInstance();
		$this->owner = intval($id);
		$db->query("UPDATE `groups` SET `owner`='{$this->owner}' WHERE `id`='" . intval($this->id) . "'");
		$this->users = null;
	}

	/**
	 * Изменяет название группы
	 * @param string $name
	 */
	public function setName($name)
	{

		if ($name == $this->name) {
			return;
		}
		$db = Difra\MySQL::getInstance();
		$this->name = $db->escape(trim($name));
		$db->query("UPDATE `groups` SET `name`='{$this->name}' WHERE `id`='{$this->id}'");
	}

	/**
	 * Изменяет имя домена группы
	 * @param string $domain
	 */
	public function setDomain($domain)
	{

		if ($domain == $this->domain) {
			return;
		}
		$db = Difra\MySQL::getInstance();
		$this->domain = $db->escape(trim($domain));
		$db->query("UPDATE `groups` SET `domain='{$this->domain}' WHERE `id`='{$this->id}'");
	}

	/**
	 * Добавляет пользователя в группу
	 * Массив $data может содержать следующие ключи:
	 * role                роль пользователя в группе (произвольная строка)
	 * confirmed        0 или 1 — подтверждение владельца группы
	 * comment        комментарий для владельца группы
	 * @param int $id
	 * @param array $data
	 */
	public function addUser($id, $data = [])
	{

		$validKeys = ['role', 'confirmed', 'comment'];
		$db = Difra\MySQL::getInstance();
		$keys = [
			"`group`='{$this->id}'",
			"`user`='" . intval($id) . "'"
		];
		if (!empty($data)) {
			foreach ($data as $k => $v) {
				if (!in_array($k, $validKeys)) {
					continue;
				}
				$v = trim($v);
				if ($v) {
					$keys[] = "`" . $db->escape($k) . "`='" . $db->escape($v) . "'";
				}
			}
		}
		$db->query("REPLACE INTO `groups_users` SET " . implode(',', $keys));
		$this->users = null;
	}

	/**
	 * Удаление пользователя из группы
	 * @param int $id
	 */
	public function delUser($id)
	{

		$db = Difra\MySQL::getInstance();
		$db->query("DELETE FROM `groups_users` WHERE `user`='" . $db->escape($id) . "' AND `group`='" .
				   $db->escape($this->id) . "'");
		$this->users = null;
	}

	/**
	 * Возвращает список участников группы
	 * $confirmed моджет содержать 0, 1 или null
	 * @param mixed $confirmed
	 * @return array
	 */
	public function getUsers($confirmed = null)
	{

		if (!is_null($this->users)) {
			return $this->users;
		}

		$db = Difra\MySQL::getInstance();
		$userList = [];
		$query = "SELECT gu.*, uf.`value` AS `nickname`
					FROM `groups_users` gu
					LEFT JOIN `users_fields` AS uf ON uf.`id`=gu.`user` AND uf.`name`='nickname'
					WHERE gu.`group`='" . $this->id . "'";
		if (!is_null($confirmed)) {
			$query .= " AND gu.`confirmed`='" . intval($confirmed) . "'";
		}
		$res = $db->fetch($query);
		if (!empty($res)) {
			foreach ($res as $data) {
				$userList[$data['user']] = $data;
			}
		}
		return $this->users = $userList;
	}

	/**
	 * Проверяет, входит ли пользователь в группу
	 * @param int $id
	 * @return bool
	 */
	public function hasUser($id)
	{

		if ($id == $this->owner) {
			return true;
		}
		$db = Difra\MySQL::getInstance();
		return (bool)$db->fetchOne(
			"SELECT count(*) FROM `groups_users` WHERE `group`='{$this->id}' AND `user`='" . $db->escape($id) .
			"' AND `confirmed`=1");
	}

	/**
	 * Устанавливает роль для пользователя в группе
	 * @param int $user
	 * @param string $role
	 */
	public function setUserRole($user, $role)
	{

		$db = Difra\MySQL::getInstance();
		$db->query("UPDATE `groups_users` SET `role`='" . $db->escape($role) .
				   "' WHERE `group`='{$this->id}' AND `user`='" . $db->escape($user)
				   . "'");
		$this->users = null;
	}

	/**
	 * Подтверждение пользователя
	 * (для модератора)
	 * @param int $id
	 * @param bool $confirmed
	 */
	public function setUserConfirmed($id, $confirmed = true)
	{

		$confirmed = $confirmed ? '1' : '0';
		$db = Difra\MySQL::getInstance();
		$db->query("UPDATE `groups_users` SET `confirmed`='{$confirmed}' WHERE `group`='{$this->id}' AND `user`='" .
				   $db->escape($id) . "'");
	}

	/**
	 * Group::setAllConfirmed()
	 * @desc Подтверждает всех пользователей в группе
	 * @param bool $confirmed
	 * @return void
	 */
	public function setAllConfirmed($confirmed = true)
	{

		$confirmed = $confirmed ? '1' : '0';
		$db = Difra\MySQL::getInstance();
		$db->query("UPDATE `groups_users` SET `confirmed`='{$confirmed}' WHERE `group`='{$this->id}' AND `confirmed`=0");
	}

	/**
	 * Group::delAllUsers()
	 * @desc Удаляет всех подтвержденных или нет пользователей из группы
	 * @param boolean $confirmed
	 * @return void
	 */
	public function delAllUsers($confirmed = false)
	{

		$confirmed = $confirmed ? '1' : '0';
		$db = \Difra\MySQL::getInstance();
		$db->query("DELETE FROM `groups_users` WHERE `confirmed`='" . $db->escape($confirmed) . "' AND `group`='" .
				   $db->escape($this->id) . "'");
		$this->users = null;
	}

	/**
	 * Group::getIdByName()
	 * @desc Возвращает id группы по его имени
	 * @param string $name
	 * @return integer
	 */
	public static function getIdByName($name)
	{

		$db = Difra\MySQL::getInstance();
		$res = $db->fetch("SELECT `id` FROM `groups` WHERE `name`='" . $db->escape($name) . "'");
		return !empty($res) ? $res[0]['id'] : false;
	}

	public function getId()
	{

		return $this->id;
	}

	/**
	 * Group::getDomain()
	 * @desс Возвращает домен группы
	 * @return string
	 */
	public function getDomain()
	{

		return $this->domain;
	}

	/**
	 * Group::getName()
	 * @desc Возвращает имя группы
	 * @return string
	 */
	public function getName()
	{

		return $this->name;
	}

	/**
	 * Group::getOwner()
	 * @desc Возвращает id владельца группы
	 * @return integer
	 */
	public function getOwner()
	{

		return $this->owner;
	}

	/**
	 * @desc Создаёт подноду group с информацией о группе
	 * @param \DOMElement $node
	 * @return void
	 */
	public function getXML($node)
	{

		/** @var \DOMElement $groupNode */
		$groupNode = $node->appendChild($node->ownerDocument->createElement('group'));
		$groupNode->setAttribute('id', $this->id);
		$groupNode->setAttribute('name', htmlspecialchars($this->name));
		//$groupNode->setAttribute( 'name', $this->name );
		$groupNode->setAttribute('domain', $this->domain);
		$groupNode->setAttribute('owner', $this->owner);
	}

	/**
	 * Group::getDomainByName()
	 * @desc Возвращает домен группы по имени
	 * @param string $name
	 * @return string
	 */
	public static function getDomainByName($name)
	{

		$db = Difra\MySQL::getInstance();
		$res = $db->fetch("SELECT `domain` FROM `groups` WHERE `name`='" . $db->escape($name) . "'");
		return isset($res[0]['domain']) ? $res[0]['domain'] : false;
	}

	/**
	 * Group::updateProfile()
	 * @desc Обновляет профиль группы
	 * @param integer $groupId
	 * @param array $data
	 * @return void
	 */
	public function updateProfile($groupId, $data)
	{

		$db = Difra\MySQL::getInstance();
		$db->query("UPDATE `groups` SET `name`='" . $db->escape($data['name']) .
				   "', `domain`='" . $db->escape($data['domain']) . "' WHERE `id`='" . intval($groupId) . "'");
		$this->name = $db->escape($data['name']);
		$this->domain = $db->escape($data['domain']);
	}

	/**
	 * @desc При вызовах с параметром запоминает группу. Возвращает текущую запомненную группу или null.
	 * @static
	 * @param int $group
	 * @return Group|null
	 */
	public static function current($group = null)
	{

		static $_group = null;
		if ($group) {
			$_group = $group;
		}
		return $_group;
	}

	/**
	 * Group::getNewGroupsXml()
	 * @desc     Создаёт xml с новыми исполнителями
	 * @param \DOMElement $node
	 * @param integer $limit
	 * @param bool $trackCountFilter - если true то в выводе будут только группы с треками.
	 * @internal param bool $fanGroup - фильтр по фангруппам 0 - без фангрупп, 1 - с фангруппами
	 * @return void
	 */
	public static function getNewGroupsXml($node, $limit = 15, $trackCountFilter = true)
	{

		$db = Difra\MySQL::getInstance();
		$limitString = '';
		if ($limit != 0) {
			$limitString = ' LIMIT ' . intval($limit);
		}

		$res = $db->fetch("SELECT g.*
					FROM `groups` g
					ORDER BY g.`id` DESC" . $limitString);
		if (!empty($res)) {
			$groupsIdArray = [];
			foreach ($res as $data) {
				$groupsIdArray[] = $data['id'];
			}

			// TODO: Tracks не должно же быть в плагине
			$tracksCount = \Tracks::getInstance()->getTracksCount($groupsIdArray);
			if (!empty($tracksCount)) {
				/** @var \DOMElement $newGroupsXml */
				$newGroupsXml = $node->appendChild($node->ownerDocument->createElement('newGroups'));
				$newGroupsXml->setAttribute('limit', $limit);
				foreach ($res as $data) {

					if ($trackCountFilter) {
						if (isset($tracksCount[$data['id']]['tracks_count']) &&
							$tracksCount[$data['id']]['tracks_count'] != 0
						) {
							/** @var \DOMElement $groupItemXml */
							$groupItemXml = $newGroupsXml->appendChild($node->ownerDocument->createElement('group'));
							foreach ($data as $key => $value) {
								$groupItemXml->setAttribute($key, $value);
							}
							$groupItemXml->setAttribute('tracks_count', $tracksCount[$data['id']]['tracks_count']);
						}
					} else {

						$tCount = 0;
						$groupItemXml = $newGroupsXml->appendChild($node->ownerDocument->createElement('group'));
						foreach ($data as $key => $value) {
							$groupItemXml->setAttribute($key, $value);
						}
						if (isset($tracksCount[$data['id']]['tracks_count'])) {
							$tCount = intval($tracksCount[$data['id']]['tracks_count']);
						}
						$groupItemXml->setAttribute('tracks_count', $tCount);
					}
				}
			}
		}
	}

	public static function saveVisible($groupId, $data)
	{

		$db = \Difra\MySQL::getInstance();
		$db->query("DELETE FROM `groups_visible` WHERE `id`='" . intval($groupId) . "'");

		if (!empty($data)) {
			$insertArray = [];
			foreach ($data as $key => $value) {
				$insertArray[] = "('" . intval($groupId) . "', '" . $db->escape($key) . "', '" . intval($value) . "')";
			}

			$query = "INSERT INTO `groups_visible` (`id`, `name`, `value`) VALUES " . implode(', ', $insertArray);
			$db->query($query);
		}
	}

	/**
	 * @static
	 * @param \DOMElement $node
	 * @param array $groupsId
	 */
	public static function getVisibleXml($node, $groupsId = null)
	{

		$whereString = '';
		if (!is_null($groupsId)) {
			$groupsId = array_map('intval', $groupsId);
			$whereString = "WHERE `id` IN (" . implode(', ', $groupsId) . ")";
		}

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT * FROM `groups_visible` " . $whereString;
		$res = $db->fetch($query);
		if (!empty($res)) {
			$visibleNode = $node->appendChild($node->ownerDocument->createElement('groups_visible'));
			foreach ($res as $data) {
				/** @var \DOMElement $visibleNodeItem */
				$visibleNodeItem = $visibleNode->appendChild($node->ownerDocument->createElement('group'));
				foreach ($data as $key => $value) {
					$visibleNodeItem->setAttribute($key, $value);
				}
			}
		}
	}

	/**
	 * Возвращает XML с названиями групп в которых состоит юзер
	 * @static
	 * @param int $userId
	 * @param \DOMNode $node
	 */
	public static function getUsersGroups($userId, $node)
	{

		$db = \Difra\MySQL::getInstance();
		$query = "SELECT gu.`group`, g.`name`, g.`domain`, gu.`role`
				FROM `groups_users` gu
				LEFT JOIN `groups` AS `g` ON g.`id`=gu.`group`
				WHERE gu.`confirmed` = 1 AND gu.`user`=" . intval($userId) . " AND gu.`group`>1";
		$db->fetchXML($node, $query);
	}
}
