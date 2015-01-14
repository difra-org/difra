<?php

namespace Difra\Plugins\Comments;

class Comment {

	private $id = null;
	private $module = null;
	private $moduleId = null;
	private $replyId = null;
	private $text = null;
	private $user = null;

	public static function create( $data ) {

		try {
			$comment           = new self;
			$db                = \Difra\MySQL::getInstance();
			$comment->module   = $db->escape( $data['module'] );
			$comment->moduleId = $db->escape( $data['moduleId'] );
			$comment->replyId  = $db->escape( $data['replyId'] );
			$comment->text     = $db->escape( $data['text'] );
			$comment->user     = $db->escape( $data['user'] );
			$comment->save();

			////// отправка письма о новом комменте
			$comment->sendMail();
		} catch( \Difra\Exception $ex ) {
			throw new \Difra\Exception( 'Failed to create new comment: ' . $ex->getMessage() );
		}
		return $comment;
	}

	private function sendMail() {

		$db = \Difra\MySQL::getInstance();

		// определяем кто владелец родительского элемента

		switch( $this->module ) {

		case 'albums':
			$query = "SELECT a.`name` AS `title`, a.`link`, a.`group_id`, g.`domain`, g.`owner`
						FROM `albums` a
						LEFT JOIN `groups` AS `g` ON g.`id`=a.`group_id`
						WHERE a.`id`='" . $this->moduleId . "'";
			break;
		case 'catalog':
			// TODO: замутить отправку письма если это ответ на коммент юзера
			// XXX: есть ведь уже? //nap
			return;
		case 'blogs':
		default:
			$query = "SELECT b.`user`, g.`owner`, bp.`title`, g.`domain`, bp.`link`
					FROM `blogs_posts` bp
					LEFT JOIN `blogs` AS `b` ON b.`id`=bp.`blog`
					LEFT JOIN `groups` AS `g` ON g.`id`=b.`group`
					WHERE bp.`id`='" . $this->moduleId . "'";
			break;
		}

		$res = $db->fetchRow( $query );

		if( empty( $res ) ) {
			return;
		}

		if( isset( $res['user'] ) && $res['user'] != '' ) {
			$elementOwner = $res['user'];
		} else {
			$elementOwner = $res['owner'];
		}

		// смотрим можно ли юзеру отправить email

		$userAdditionals = \Difra\Additionals::getAdditionals( 'users', $elementOwner );

		if( !isset( $userAdditionals['unsubscribe'] ) || $userAdditionals['unsubscribe'] == 0 ) {

			// отправляем письмо владельцу родительского элемента
			$query = "SELECT `email`, `activation` FROM `users` WHERE `id`='" . intval( $elementOwner ) . "' AND `banned`=0 AND `active`=1";

			$replyText = array();
			if( $this->replyId ) {
				// это ответ на чужой коммент.
				// забираем данные об ответе
				$query     = "SELECT c.`text`, c.`user` FROM `" . $this->module . "_comments` c WHERE c.`id`='" . $this->replyId . "'";
				$replyText = $db->fetchRow( $query );
				$query     = "SELECT `email`, `activation` FROM `users` WHERE `id`='" . intval( $replyText['user'] ) .
					     "' AND `banned`=0 AND `active`=1";
			}

			$userData = $db->fetchRow( $query );

			if( !empty( $userData ) ) {

				// получаем никнейм отправителя:
				$replyUser = \Difra\Additionals::getAdditionalValue( 'users', $this->user, 'nickname' );

				// ссылка на родительский элемент
				$elementLink = '';
				if( $this->module == 'albums' ) {

					// если альбом
					$elementLink = 'http://' . $res['domain'] . '.' . \Difra\Site::getInstance()->getMainhost() .
						       '/album/' . rawurlencode( $res['link'] ) . '/';
				} elseif( $this->module == 'blogs' ) {

					if( isset( $res['domain'] ) && $res['domain'] != '' ) {

						// если пост в блоге группы
						$elementLink = 'http://' . $res['domain'] . '.' . \Difra\Site::getInstance()->getMainhost() .
							       '/' . $this->moduleId . '/' . rawurlencode( $res['link'] ) . '/';
					} else {

						// если пост в личном блоге юзера
						$ownerNickname = \Difra\Additionals::getAdditionalValue( 'users', $elementOwner, 'nickname' );
						$elementLink   = 'http://' . \Difra\Site::getInstance()->getMainhost() . '/blogs/' . $ownerNickname .
								 '/' . $this->moduleId . '/' . rawurlencode( $res['link'] ) . '/';
					}
				}

				$unsubscribeLink = 'http://' . \Difra\Site::getInstance()->getMainhost() . '/unsubscribe/' . $userData['activation'] . '/';

				$sendData = array(
					'unsubscribe'    => $unsubscribeLink,
					'message'        => $this->text,
					'module'         => $this->module,
					'link'           => $elementLink,
					'reply_nickname' => $replyUser,
					'mainHost'       => \Difra\Site::getInstance()->getMainhost(),
					'title'          => $res['title']
				);

				if( $this->replyId ) {
					$sendData['replay']   = 1;
					$sendData['original'] = $replyText['text'];
				}

				\Difra\Mailer::getInstance()->CreateMail( $userData['email'], 'mail_newcomment', $sendData );
			}
		}
	}

	private function save() {

		$db = \Difra\MySQL::getInstance();
		$db->query( "REPLACE `{$this->module}_comments` SET "
			    . ( $this->id ? "`id`='{$this->id}'," : '' )
			    . "`user`='{$this->user}',"
			    . "`parent_id`=" . ( $this->moduleId ? "'{$this->moduleId}'" : 'NULL' ) . ','
			    . "`reply_id`=" . ( $this->replyId ? "'{$this->replyId}'" : 'NULL' ) . ','
			    . "`text`='{$this->text}'" );
		if( $db->getAffectedRows() == 1 ) {
			$this->id = $db->getLastId();
		}
	}

	/**
	 * Возвращает кол-во комментариев
	 * @static
	 *
	 * @param $module
	 * @param $moduleId
	 *
	 * @return int
	 */
	public static function getCommentsCount( $module, $moduleId ) {

		$db     = \Difra\MySQL::getInstance();
		$module = $db->escape( $module );

		$query = "SELECT COUNT( `id` ) AS `count` FROM `{$module}_comments` WHERE `parent_id`='" . intval( $moduleId ) . "'";
		$res   = $db->fetchRow( $query );
		return isset( $res['count'] ) ? $res['count'] : 0;
	}

	/**
	 * Возвращает XML с кол-во комментариев для записей в модуле
	 *
	 * @static
	 *
	 * @param \DOMNode $node
	 * @param array    $idArray
	 * @param string   $module
	 */
	public static function getCommentsCountInIdsXML( $node, $idArray, $module ) {

		$db      = \Difra\MySQL::getInstance();
		$module  = $db->escape( $module );
		$idArray = array_map( 'intval', $idArray );

		$query = "SELECT COUNT(`id`) AS `count`, `parent_id` FROM `{$module}_comments`
				WHERE `parent_id` IN (" . implode( ', ', $idArray ) . ") GROUP BY `parent_id`";
		$res   = $db->fetch( $query );

		if( !empty( $res ) ) {
			$commentCountNode = $node->appendChild( $node->ownerDocument->createElement( 'commentsCount' ) );
			foreach( $res as $data ) {
				/** @var \DOMElement $itemNode */
				$itemNode = $commentCountNode->appendChild( $node->ownerDocument->createElement( 'item' ) );
				$itemNode->setAttribute( 'id', $data['parent_id'] );
				$itemNode->setAttribute( 'count', $data['count'] );
			}
		}
	}

	/**
	 * @static
	 *
	 * @param \DOMElement $node
	 * @param string      $module
	 * @param int         $moduleId
	 */
	public static function getCommentsXML( $node, $module, $moduleId ) {

		$db     = \Difra\MySQL::getInstance();
		$module = $db->escape( $module );

		// определение владельца элемента которому принадлежит комментарий
		// TODO: из-за того что сделали разную структуру таблиц альбомов и блогов придётся выдёргивать id для каждого модуля отдельно. (
		$parentOwner = false;
		switch( $module ) {
		case 'blogs':
			$query = "SELECT bl.`user`, bl.`group`
						FROM `blogs_posts` bp
						RIGHT JOIN `blogs` AS `bl` ON bl.`id`=bp.`blog`
						WHERE bp.`id`='" . intval( $moduleId ) . "'";
			break;
		case 'albums':
			$query = "SELECT al.`group_id` as `group`
						FROM `albums` al
						WHERE al.`id` = '" . intval( $moduleId ) . "'";
			break;
		default:
			$query = false;
		}

		if( $query ) {
			$parentOwner = $db->fetchRow( $query );
		}

		// забираем комментарии
		$commentsData = $db->fetch( "SELECT `{$module}_comments`.*,`users_fields`.`value` AS `nickname` FROM `{$module}_comments` "
					    . "LEFT JOIN `users_fields` ON `{$module}_comments`.`user`=`users_fields`.`id` "
					    . "WHERE `parent_id`='" . $db->escape( $moduleId ) . "' AND `users_fields`.`name`='nickname'" );

		if( empty( $commentsData ) ) {
			return;
		}

		$groups = array();
		$Auth   = \Difra\Auth::getInstance();
		$userId = $Auth->getId();
		if( $userId && \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {
			$groups = \Difra\Plugins\Blogs\Group::getOwnedGroupsIds( $userId );
		}

		foreach( $commentsData as $comment ) {

			/** @var \DOMElement $commentNode */
			$commentNode = $node->appendChild( $node->ownerDocument->createElement( 'comment' ) );
			foreach( $comment as $k => $v ) {
				$commentNode->setAttribute( $k, $v );
			}
			$commentNode->setAttribute( 'nickURL', rawurlencode( $comment['nickname'] ) );

			if( $userId && ( $userId == $comment['user'] || $Auth->isModerator() ) ) {
				$commentNode->setAttribute( 'canModify', true );
			} elseif( $userId && $parentOwner && in_array( $parentOwner['group'], $groups ) ) {
				$commentNode->setAttribute( 'canModify', true );
			} elseif( $userId && $parentOwner && isset( $parentOwner['user'] ) && $parentOwner['user'] == $userId ) {
				$commentNode->setAttribute( 'canModify', true );
			}
		}
	}

	public static function checkDeleteRights( $id, $module ) {

		$db          = \Difra\MySQL::getInstance();
		$parentOwner = false;
		switch( $module ) {
		case 'blogs':
			$query = "SELECT bl.`user`, bl.`group`
						FROM `blogs_posts` bp
						RIGHT JOIN `blogs` AS `bl` ON bl.`id`=bp.`blog`
						WHERE bp.`id`='" . intval( $id ) . "'";
			break;
		case 'albums':
			$query = "SELECT al.`group_id` as `group`
						FROM `albums` al
						WHERE al.`id` = '" . intval( $id ) . "'";
			break;
		default:
			$query = false;
		}

		if( $query ) {
			$parentOwner = $db->fetchRow( $query );
		}

		$groups = array();
		$Auth   = \Difra\Auth::getInstance();
		$userId = $Auth->getId();
		if( $userId && \Difra\Plugger::getInstance()->isEnabled( 'blogs' ) ) {
			$groups = \Difra\Plugins\Blogs\Group::getOwnedGroupsIds( $userId );
		}

		$commentData = $db->fetchRow( "SELECT `user` FROM `{$module}_comments` WHERE `id`='" . intval( $id ) . "'" );

		if( $userId && ( $userId == $commentData['user'] || $Auth->isModerator() ) ) {
			return true;
		} elseif( $userId && $parentOwner && in_array( $parentOwner['group'], $groups ) ) {
			return true;
		} elseif( $userId && $parentOwner && isset( $parentOwner['user'] ) && $parentOwner['user'] == $userId ) {
			return true;
		}
		return false;
	}

	public static function delete( $id, $module ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `{$module}_comments` WHERE `id`='" . intval( $id ) . "'" );
		return true;
	}
}