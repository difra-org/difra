<?php

use Difra\Plugins\Blogs, Difra\Param;

class BlogsPostController extends Difra\Controller
{
    public function dispatch()
    {

        if ($group = Blogs\Group::current()) {
            $group->getXML($this->root);
            $this->root->setAttribute('currentGroup', $group->getDomain());
        }

        // группы юзера
        \Difra\Plugins\Blogs::getInstance()->getUserGroupsXML($this->root);
    }

    public function newActionAuth()
    {

        if (Blogs\Group::current() and !Blogs\Group::current()->hasUser(Difra\Auth::getInstance()->getEmail())) {
            \Difra\Libs\Cookies::getInstance()->notify(\Difra\Locales::getInstance()
                                                                     ->getXPath('blogs/notifies/add_post_denied'),
                true);
            $this->view->redirect('/');
        }

        // создаём временный пост с visible = 0;
        $userId = Difra\Auth::getInstance()->getEmail();
        if ($group = Blogs\Group::current()) {
            if (!$group->hasUser($userId)) {
                \Difra\Libs\Cookies::getInstance()->notify(\Difra\Locales::getInstance()
                                                                         ->getXPath('blogs/notifies/add_post_denied'),
                    true);
                $this->view->redirect('/');
            }
            $blog = Blogs\Blog::touchByGroup($group->getId());
        } else {
            $blog = Blogs\Blog::touchByUser($userId);
        }
        if ($post = $blog->addPost($userId, '', '', true)) {
            $this->view->redirect('/blogs/post/edit/' . $post->getId() . '/');
        } else {
            $this->ajax->error(\Difra\Locales::getInstance()->getXPath('blogs/notifies/add_post_failed'));
        }
        //$this->root->appendChild( $this->xml->createElement( 'blogsPostNew' ) );
    }

    public function editActionAuth(Param\AnyInt $id)
    {

        if (!$post = Blogs\Post::getById($id)) {
            \Difra\Libs\Cookies::getInstance()->notify(\Difra\Locales::getInstance()
                                                                     ->getXPath('blogs/notifies/post_not_found'), true);
            $this->view->redirect('/');
        }
        $Auth = \Difra\Auth::getInstance();
        if ($post->getUser() != $Auth->getEmail() && !$Auth->isModerator()) {
            $group = $post->getBlog()->getGroup();
            if (!$group or $group->getOwner() != \Difra\Auth::getInstance()->getEmail()) {
                \Difra\Libs\Cookies::getInstance()->notify(\Difra\Locales::getInstance()
                                                                         ->getXPath('blogs/notifies/edit_post_denied'),
                    true);
                $this->view->redirect('/');
            }
        }
        $rootNode = $this->root->appendChild($this->xml->createElement('left'));
        /** @var \DOMElement $editNode */
        $editNode = $rootNode->appendChild($this->xml->createElement('blogsPostEdit'));
        $post->getXML($editNode, true);
        if ($tags = \Difra\Plugins\Tags::getInstance()->get('posts', $id->val())) {
            $tags = array_map(function ($t) {

                return $t['tag'];
            }, $tags);
            $tags = implode(', ', $tags);
            $editNode->setAttribute('tags', $tags);
        }
        // необходимо запомнить текущий редактируемый пост, что бы при загрузке картинки загружать туда, куда нужно
        $_SESSION['editPost'] = $post->getId();
    }

    public function addAjaxActionAuth(Param\AjaxString $title, Param\AjaxSafeHTML $text, Param\AjaxString $tags = null)
    {

        $userId = Difra\Auth::getInstance()->getEmail();
        if ($group = Blogs\Group::current()) {
            if (!$group->hasUser($userId)) {
                \Difra\Libs\Cookies::getInstance()->notify(\Difra\Locales::getInstance()
                                                                         ->getXPath('blogs/notifies/add_post_denied'),
                    true);
                $this->view->redirect('/');
            }
            $blog = Blogs\Blog::touchByGroup($group->getId());
        } else {
            $blog = Blogs\Blog::touchByUser($userId);
        }
        if ($post = $blog->addPost($userId, $title->val(), $text->val())) {
            $post = $blog->getPost($post->getId());
            if (class_exists('Difra\Plugins\Tags')) {
                $tagsArray = Difra\Plugins\Tags::getInstance()->tagsFromString($tags);
                Difra\Plugins\Tags::getInstance()->update('posts', $post->getId(), $tagsArray);
            }
            $this->ajax->redirect($post->getUrl());
        } else {
            $this->ajax->error(\Difra\Locales::getInstance()->getXPath('blogs/notifies/add_post_failed'));
        }
    }

    public function updateAjaxActionAuth(
        Param\AjaxInt $id,
        Param\AjaxString $title,
        Param\AjaxSafeHTML $text,
        Param\AjaxString $tags = null
    ) {

        if (!$post = Blogs\Post::getById($id)) {
            \Difra\Libs\Cookies::getInstance()->notify(\Difra\Locales::getInstance()
                                                                     ->getXPath('blogs/notifies/post_not_found'), true);
            $this->view->redirect('/');
        }
        $Auth = \Difra\Auth::getInstance();
        if ($post->getUser() != $Auth->getEmail() && !$Auth->isModerator()) {
            $group = $post->getBlog()->getGroup();
            if (!$group or $group->getOwner() != \Difra\Auth::getInstance()->getEmail()) {
                \Difra\Libs\Cookies::getInstance()->notify(\Difra\Locales::getInstance()
                                                                         ->getXPath('blogs/notifies/edit_post_denied'),
                    true);
                $this->view->redirect('/');
            }
        }

        $post->setTitle($title->val());
        $post->setText($text->val());
        $post->setVisible(1);
        $post->save();

        $post = $post->getBlog()->getPost($post->getId());
        if (class_exists('Difra\Plugins\Tags')) {
            $tagsArray = Difra\Plugins\Tags::getInstance()->tagsFromString($tags);
            Difra\Plugins\Tags::getInstance()->update('posts', $id->val(), $tagsArray);
        }

        // убираем метку о редактировании поста
        unset($_SESSION['editPost']);

        $this->ajax->redirect($post->getUrl());
    }

    public function deletenotifyAjaxActionAuth(Param\AjaxString $id = null)
    {

        $id = $id ? $id->val() : null;
        if (!$post = Blogs\Post::getById($id)) {
            $this->ajax->display(Difra\Locales::getInstance()->getXPath('blogs/notifies/post_not_found'));
            die();
        }
        $Auth = \Difra\Auth::getInstance();
        if ($post->getUser() != $Auth->getEmail() && !$Auth->isModerator()) {
            $group = $post->getBlog()->getGroup();
            if (!$group or $group->getOwner() != \Difra\Auth::getInstance()->getEmail()) {
                $this->ajax->display(Difra\Locales::getInstance()->getXPath('blogs/notifies/edit_post_denied'));
                die();
            }
        }

        $nickname = \Difra\Additionals::getAdditionalValue('users', $post->getUser(), 'nickname');

        $this->ajax->display(Difra\Locales::getInstance()->getXPath('blogs/notifies/delete_post') .
                             '<br/><br/><div href="#" onclick="blogs.delete( '
                             . intval($id) . ', \'' . $nickname . '\' );" class="button">Да</div>'
                             .
                             '<a href="#" style="display: inline-block; margin-left:15px;" class="button" onclick="ajaxer.close(this)">Нет</a>');
    }

    public function deleteAjaxActionAuth(Param\AjaxString $id = null)
    {

        $id = $id ? $id->val() : null;
        if (!$post = Blogs\Post::getById($id)) {
            $this->ajax->display(Difra\Locales::getInstance()->getXPath('blogs/notifies/post_not_found'));
            die();
        }
        $Auth = \Difra\Auth::getInstance();
        if ($post->getUser() != $Auth->getEmail() && !$Auth->isModerator()) {
            $group = $post->getBlog()->getGroup();
            if (!$group or $group->getOwner() != \Difra\Auth::getInstance()->getEmail()) {
                $this->ajax->display(Difra\Locales::getInstance()->getXPath('blogs/notifies/edit_post_denied'));
                die();
            }
        }

        $post::delete($id);
        $this->ajax->setResponse('success', true);
    }

    public function indexAction()
    {

        $this->view->httpError(404);
    }
}
