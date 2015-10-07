<?php

class BlogsFriendsController extends \Difra\Controller
{
    public function addAjaxAction(\Difra\Param\AnyString $blogId)
    {

        if (!\Difra\Auth::getInstance()->getEmail()) {
            $this->ajax->notify(Difra\Locales::getInstance()->getXPath('notify/need_reg'));
            return;
        }
        $blog = \Difra\Plugins\Blogs::getInstance();
        $blog->addFriend($blogId);
        // TODO: вывод ошибок
        $this->ajax->redirect($_SERVER['HTTP_REFERER']);
    }

    public function deleteAjaxActionAuth(\Difra\Param\AnyString $blogId)
    {

        $blog = \Difra\Plugins\Blogs::getInstance();
        $blog->delFriend($blogId);
        // TODO: вывод ошибок
        $this->ajax->redirect($_SERVER['HTTP_REFERER']);
    }
}
