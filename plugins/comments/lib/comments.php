<?php

namespace Difra\Plugins;

class Comments {

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function getCommentsXML( $node, $module, $moduleId ) {

		$node->setAttribute( 'module', $module );
		$node->setAttribute( 'id', $moduleId );
		Comments\Comment::getCommentsXML( $node, $module, $moduleId );
	}

	public function add( $data ) {

		// module moduleId replyId newComment
		if( empty( $data['module'] ) or !trim( $data['module'] ) ) {
			throw new \Difra\Exception( 'Missing module name for comments' );
		}
		if( empty( $data['moduleId'] ) ) {
			throw new \Difra\Exception( 'Comments module id is missing or invalid' );
		}
		if( empty( $data['replyId'] ) ) {
			$data['replyId'] = 0;
		}
		if( empty( $data['text'] ) or !trim( $data['text'] ) ) {
			return false;
		}
		$auth = \Difra\Auth::getInstance();
		$auth->required();
		$data['user'] = $auth->getId();

		return Comments\Comment::create( $data );
	}

	/**
	 * Возвращает XML со всеми комментариями по всем описанным в конфиге модулям
	 * @param \DOMElement $node
	 */
	public function getAllCommentsXML( $node ) {

		$modules = \Difra\Config::getInstance()->get( 'comments' );

		if( empty( $modules ) ) {
			$node->appendChild( $node->ownerDocument->createElement( 'empty' ) );
			return;
		}

		$db = \Difra\MySQL::getInstance();

		foreach( $modules['modules'] as $module ) {
			$moduleXML = $node->appendChild( $node->ownerDocument->createElement( $module . '_comments' ) );
			$moduleXML->setAttribute( 'module', $module );

			// забираем комментарии по модулю
			$query = '';
			switch( $module ) {

				case 'albums':
					$query = "SELECT c.`id`, c.`text`, c.`reply_id`, DATE_FORMAT( c.`date`, '%T %d-%m-%Y' ) as `date`,
								uf.`value` AS `nickname`, bp.`link` AS `post_link`, bp.`name` AS `title`, g.`domain`,
								bp.`id` AS `post_id`
							FROM `albums_comments` c
							LEFT JOIN `users_fields` AS `uf` ON uf.`id`=c.`user` AND uf.`name`='nickname'
							LEFT JOIN `albums` AS `bp` ON bp.`id`=c.`parent_id`
							LEFT JOIN `groups` AS `g` ON g.`id`=bp.`group_id`
							ORDER BY c.`date` DESC";
					break;

				case 'blogs':
					$query = "SELECT c.`id`, c.`user`, c.`text`, c.`reply_id`, bp.`title`, b.`user` `post_owner`, b.`group`,
								g.`domain`, of.`value` AS `owner_nickname`, bp.`link` AS `post_link`, bp.`id` AS `post_id`,
								DATE_FORMAT( c.`date`, '%T %d-%m-%Y' ) as `date`, uf.`value` AS `nickname`
							FROM `blogs_comments` `c`
							LEFT JOIN `users_fields` AS `uf` ON uf.`id`=c.`user` AND uf.`name`='nickname'
							LEFT JOIN `blogs_posts` AS `bp` ON bp.`id`=c.`parent_id`
							LEFT JOIN `blogs` AS `b` ON b.`id`=bp.`blog`
							LEFT JOIN `groups` AS `g` ON g.`id`=b.`group`
							LEFT JOIN `users_fields` AS `of` ON of.`id`=b.`user` AND of.`name`='nickname'
							ORDER BY c.`date` DESC";
					break;

				case 'catalog':
					$query = "SELECT c.`id`, c.`text`, c.`reply_id`, DATE_FORMAT( c.`date`, '%T %d-%m-%Y' ) as `date`,
								uf.`value` AS `nickname`, bp.`name` AS `title`
							FROM `catalog_comments` c
							LEFT JOIN `users_fields` AS `uf` ON uf.`id`=c.`user` AND uf.`name`='nickname'
							LEFT JOIN `catalog_items` AS `bp` ON bp.`id`=c.`parent_id`
							ORDER BY c.`date` DESC";
					break;
			}

			$res = $db->fetch( $query );

			foreach( $res as $data ) {
				/** @var $itemNode \DOMElement */
				$itemNode = $moduleXML->appendChild( $node->ownerDocument->createElement( 'item' ) );
				$itemNode->setAttribute( 'id', $data['id'] );
				$itemNode->setAttribute( 'nickname', $data['nickname'] );
				$itemNode->setAttribute( 'date', $data['date'] );
				$itemNode->setAttribute( 'text', $data['text'] );
				$itemNode->setAttribute( 'reply_id', $data['reply_id'] );
				$itemNode->setAttribute( 'title', $data['title'] );

				$link = 'http://';

				switch( $module ) {

					case 'blogs':
						if( $data['domain'] != '' ) {
							// ссылка на блог группы
							$link .=
								$data['domain'] . '.' . \Difra\Site::getInstance()->getMainhost() . '/' . $data['post_id'] . '/'
									. rawurlencode( $data['post_link'] ) . '/#comment' . $data['id'];
						} else {
							// ссылка на блог юзера
							$link .= \Difra\Site::getInstance()->getMainhost() . '/blogs/' . $data['owner_nickname'] . '/'
								. $data['post_id'] . '/' . rawurldecode( $data['post_link'] ) . '/#comment' . $data['id'];
						}
						break;
					case 'albums':
						$link = 'http://' . $data['domain'] . '.' . \Difra\Site::getInstance()->getMainhost() . '/album/'
							. rawurldecode( $data['post_link'] ) . '/#comment' . $data['id'];
						break;
					case 'catalog':
						$link = 'http://' . \Difra\Site::getInstance()->getMainhost() . '/c/';

						break;

				}

				$itemNode->setAttribute( 'parentLink', $link );

			}
		}
	}

}