<?php

namespace Difra\Plugins\Portfolio;
use Difra;

class Contributors {

	/**
	 * Добавляет или обновляет юзера портфолио
	 * @param int      $id
	 * @param string   $name
	 * @param string   $linkText
	 * @param string   $description
	 * @param string   $role
	 * @param int      $archive
	 */
	public static function saveContributor( $id, $name, $linkText = null, $description = null, $role = null, $archive = 0 ) {
		
		$db = Difra\MySQL::getInstance();
		$query = "INSERT INTO `portfolio_users` SET `id`='" . intval( $id ) . "', `name`='" . $db->escape( $name ) . 
			"', `linktext`='" . $db->escape( $linkText ) . "', `description`='" . $db->escape( $description ) . 
			"', `archive`=" . intval( $archive ) . ", `role`='" . $db->escape( $role ) . "' 
			ON DUPLICATE KEY UPDATE `name`='" . $db->escape( $name ) . 
			"', `linktext`='" . $db->escape( $linkText ) . "', `description`='" . $db->escape( $description ) . 
			"',`archive`='" . intval( $archive ) . "', `role`='" . $db->escape( $role ) . "'";

		$db->query( $query );

	}
	
	/**
	 * Возвращает список юзеров портфолио
	 * @return array
	 */
	public static function getContributors() {

		$db = Difra\MySQL::getInstance();
		$query = "SELECT c.`id`, c.`name`, u.`email`, c.`archive`, c.`linktext`, c.`role` 
					FROM `portfolio_users` c
					LEFT JOIN `users` AS `u` ON u.`id`=c.`id`";
		return $db->fetch( $query );
	}

	/**
	 * Удаляет участника портфолио
	 * @param int $id
	 */
	public static function delContributor( $id ) {
		$db = Difra\MySQL::getInstance();
		echo "DELETE FROM `portfolio_users` WHERE `id`='" . intval( $id ) . "'";
		$db->query( "DELETE FROM `portfolio_users` WHERE `id`='" . intval( $id ) . "'" );
	}

	/**
	 * Contributors::getContributor()
	 * @desc Возвращает данные участника портфолио  
	 * @param mixed $id
	 * @return array
	 */
	public static function getContributor( $id ) {
		$db = Difra\MySQL::getInstance();
		return $db->fetchRow( "SELECT * FROM `portfolio_users` WHERE `id`='" . intval( $id ) . "'" );
	}

	/**
	 * Contributors::saveWorkContributors()
	 *
	 * @desc Привязывает участников к работе
	 *
	 * @param integer $workId
	 * @param array   $contributors
	 * @param array   $roles
	 */
	public static function saveWorkContributors( $workId, $contributors, $roles ) {

		$db = Difra\MySQL::getInstance();

		$db->query( "DELETE FROM `portfolio_work_to_user` WHERE `work_id`='" . intval( $workId ) . "'" );
		$insertArray = array();
		foreach( $contributors as $k=>$userId ) {
			if( isset( $roles[$k] ) && $roles[$k]!='' ) {
				$insertArray[] = "('" . intval( $workId ) . "', '" . intval( $userId ) . "', '" . $db->escape( $roles[$k] ) . "')";	
			} else {
				$insertArray[] = "('" . intval( $workId ) . "', '" . intval( $userId ) . "', NULL)";
			}
		}
		$query = "INSERT INTO `portfolio_work_to_user` (`work_id`, `user_id`, `role`) VALUES " . implode( ', ', $insertArray );
		$db->query( $query );
	}

	/**
	 * Возвращает массив в формате [id работы][i][данные участника]
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getContributorsToWork( $id = null ) {
		
		$db = Difra\MySQL::getInstance();
		$query = "SELECT pwtu.*, pu.`name`, pu.`archive`, pu.`linktext`
					FROM `portfolio_work_to_user` pwtu
					LEFT JOIN `portfolio_users` AS `pu` ON pu.`id`=pwtu.`user_id`";
		if( !is_null( $id ) ) {
			$query .= " WHERE pwtu.`work_id`='" . $id . "'";
		}

		$res = $db->fetch( $query );
		$returnArray = array();
		foreach( $res as $data ) {
			$returnArray[$data['work_id']][] = $data;
		}
		return $returnArray;
	}

}
