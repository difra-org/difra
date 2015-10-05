<?php

use Difra\Param, Difra\Plugins\Blogs;

class BlogsIndexController extends \Difra\Controller
{
	public function dispatch()
	{

		if ($Group = Blogs\Group::current()) {
			$Group->getXML($this->root);
			$this->root->setAttribute('currentGroup', $Group->getDomain());
		}

		// группы юзера
		\Difra\Plugins\Blogs::getInstance()->getUserGroupsXML($this->root);
	}

	public function indexAction(Param\AnyString $nickname = null, Param\NamedInt $page = null)
	{

		$page = $page ? $page->val() : 1;

		if ($nickname) {
			// получаем $userId по никнейму
			$nickname = rawurldecode($nickname);
			if (!$userId = Difra\Additionals::getAdditionalId('users', 'nickname', $nickname)) {
				$this->view->httpError(404);
				return;
			}
			/** @var \DOMElement $userNode */
			$userNode = $this->root->appendChild($this->xml->createElement('user'));
			$userNode->setAttribute('id', $userId);
			\Difra\Additionals::getAdditionalsXml('users', $userId, $userNode);

			// /user/имя
			if (empty($this->action->parameters)) {
				$auth = \Difra\Auth::getInstance();
				$canModify = ($auth->logged and ($userId == $auth->getEmail()));

				// виджет данных юзера
				/** @var \DOMElement $blogsViewNode */
				$blogsViewNode = $this->root->appendChild($this->xml->createElement('userInfoWidget'));
				$blogsViewNode->setAttribute('left', 1);

				$blogsViewNode = $this->root->appendChild($this->xml->createElement('blogsView'));
				$blogsViewNode->setAttribute('left', 1);
				$blogsViewNode->setAttribute('link', '/blogs/' . rawurlencode($nickname));
				$blogsViewNode->setAttribute('canModify', $canModify ? '1' : '0');
				$blogId = Blogs::getInstance()->getUserBlogXML($blogsViewNode, $userId, $page);

				if ($auth->logged) {
					if ($canModify) {
						/** @var \DOMElement $blogsControlNode */
						$blogsControlNode = $this->root->appendChild($this->xml->createElement('blogsControl'));
						$blogsControlNode->setAttribute('right', 1);
						$blogsControlNode->setAttribute('addPrefix', 1);
					}
				}

				// виджет "я в группах"
				/** @var \DOMElement $myGroupsNode */
				$myGroupsNode = $this->root->appendChild($this->xml->createElement('myGroupsWidget'));
				$myGroupsNode->setAttribute('right', 1);
				\Difra\Plugins\Blogs\Group::getUsersGroups($userId, $myGroupsNode);

				// виджет избранных блогов
				/** @var \DOMElement $friendsNode */
				$friendsNode = $this->root->appendChild($this->xml->createElement('friendsWidget'));
				$friendsNode->setAttribute('right', 1);

				\Difra\Plugins\Blogs\Blog::getFriendsPreviewXML($auth->getEmail(), $friendsNode);
				if ($userId != $auth->getEmail()) {
					$friendsNode->setAttribute('user', $auth->getEmail());
					$friendsNode->setAttribute('canAdd', $blogId);
				}
				// /user/имя/15/заголовок
			} elseif (sizeof($this->action->parameters) == 2) {
				$postId = $this->action->parameters[0];
				if (!ctype_digit($postId)) {
					$this->view->httpError(404);
					return;
				}
				$postLink = rawurldecode($this->action->parameters[1]);
				if (!$post = Blogs::getInstance()->getPost($userId, $postId)) {
					$this->view->httpError(404);
					return;
				}
				if ($postLink != $post->getLink()) {
					$this->view->redirect("/blogs/$nickname/$postId/" . $post->getLink());
					return;
				}
				$this->action->parameters = [];

				// виджет "я в группах"
				$myGroupsNode = $this->root->appendChild($this->xml->createElement('myGroupsWidget'));
				$myGroupsNode->setAttribute('right', 1);
				\Difra\Plugins\Blogs\Group::getUsersGroups($userId, $myGroupsNode);

				// виджет данных юзера
				$blogsViewNode = $this->root->appendChild($this->xml->createElement('userInfoWidget'));
				$blogsViewNode->setAttribute('left', 1);

				/** @var \DOMElement $blogsSingle */
				$blogsSingle = $this->root->appendChild($this->xml->createElement('blogsSingle'));
				$blogsSingle->setAttribute('left', 1);
				$post->getXML($blogsSingle, true);
				/** @var \DOMElement $comments */
				$comments = $this->root->appendChild($this->xml->createElement('comments'));
				$comments->setAttribute('left', 1);
				\Difra\Plugins\Comments::getInstance()->getCommentsXML($comments, 'blogs', $postId, $page);

				// виджет избранных блогов
				$friendsNode = $this->root->appendChild($this->xml->createElement('friendsWidget'));
				$friendsNode->setAttribute('right', 1);

				$auth = \Difra\Auth::getInstance();
				\Difra\Plugins\Blogs\Blog::getFriendsPreviewXML($auth->getEmail(), $friendsNode);
				if ($userId != $auth->getEmail()) {
					$friendsNode->setAttribute('user', $auth->getEmail());
					$friendsNode->setAttribute('canAdd', $post->getBlogId());
				}

				// статистика для поста
				Blogs::getInstance()->savePostStat($postId, null, $userId);
			} else {
				$this->view->httpError(404);
			}
		} else {
			$blogsViewNode = $this->root->appendChild($this->xml->createElement('blogsAllView'));
			$blogsViewNode->setAttribute('left', 1);
			$blogsViewNode->setAttribute('link', '/blogs');
			Difra\Plugins\Blogs::getInstance()->getAllPostsXML($blogsViewNode, $page);

			if (Difra\Auth::getInstance()->isLogged()) {
				/** @var \DOMElement $mypageWidget */
				$mypageWidget = $this->root->appendChild($this->xml->createElement('myPageWidget'));
				$mypageWidget->setAttribute('right', 1);
			}
			/** @var \DOMElement $controlNode */
			$controlNode = $this->root->appendChild($this->xml->createElement('artistControl'));
			$controlNode->setAttribute('right', 1);

			// TODO: вынести работу с тэгами в отдельный диспатчер
			$Tags = Difra\Plugins\Tags::getInstance();
			if ($Tags->getCloudXml('posts', $this->root)) {
				$controlNode = $this->root->appendChild($this->xml->createElement('postsTags'));
				$controlNode->setAttribute('right', 1);
			}
		}
	}

	public function tagsAction(Param\NamedInt $page = null, Param\AnyString $tagName = null)
	{

		$page = $page ? $page->val() : 1;
		/** @var \DOMElement $blogsViewNode */
		$blogsViewNode = $this->root->appendChild($this->xml->createElement('blogsTagsView'));
		$blogsViewNode->setAttribute('left', 1);

		$Tags = Difra\Plugins\Tags::getInstance();
		if ($tagName) {
			/** @var \DOMElement $currentTagNode */
			$currentTagNode = $this->root->appendChild($this->xml->createElement('currentTag', $tagName->val()));
			$blogsViewNode->setAttribute('link', '/blogs/tags/' . $tagName->val());
			$tagName = rawurldecode($tagName->val());
			$currentTagNode->setAttribute('name', $Tags->getTagByLink('posts', $tagName));
			$tagItems = $Tags->getItemsByLink('posts', $tagName);
			Difra\Plugins\Blogs::getInstance()->getAllPostsXML($blogsViewNode, $page, $tagItems);
		} else {
			$blogsViewNode->setAttribute('link', '/blogs/tags');
			Difra\Plugins\Blogs::getInstance()->getAllPostsXML($blogsViewNode, $page);
		}

		if (Difra\Auth::getInstance()->isLogged()) {
			/** @var \DOMElement $mypageWidgetNode */
			$mypageWidgetNode = $this->root->appendChild($this->xml->createElement('myPageWidget'));
			$mypageWidgetNode->setAttribute('right', 1);
		}

		/** @var \DOMElement $controlNode */
		$controlNode = $this->root->appendChild($this->xml->createElement('artistControl'));
		$controlNode->setAttribute('right', 1);

		if ($Tags->getCloudXml('posts', $this->root)) {
			/** @var \DOMElement $tagsNode */
			$tagsNode = $this->root->appendChild($this->xml->createElement('postsTags'));
			$tagsNode->setAttribute('right', 1);
		}
	}
}
