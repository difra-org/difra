<?php

use Difra\Plugins, Difra\Plugins\Blogs, Difra\Param;

class AdmBlogsController extends Difra\Controller {

	public function dispatch() {
		$this->view->instance = 'adm';
	}

	public function indexAction( Param\NamedInt $page = null ) {

		$page = $page ? $page->val() : 1;
		$this->root->appendChild( $this->xml->createElement( 'blogs-overView' ) );
		
		$blogsNode = $this->root->appendChild( $this->xml->createElement( 'blogs' ) );
		Blogs::getInstance()->getGroupBlogXML( $blogsNode, 1, $page, 10, true );

	}

	public function addpostAction() {

		$this->root->appendChild( $this->xml->createElement( 'blogs-addPost' ) );
		$Users = Difra\Plugins\Users::getInstance();
		$usersXml = $this->root->appendChild( $this->xml->createElement( 'users' ) );
		$Users->getListXML(  $usersXml );

	}

	public function savepostAction() {

		if( isset( $_POST['user'] ) && $_POST['user']!='' && isset( $_POST['postText'] ) && $_POST['postText']!='' 
			&& isset( $_POST['postTitle'] ) && $_POST['postTitle']!='' ) {
			
			$userId = intval( $_POST['user'] );

			$blog = Blogs\Blog::touchByGroup( 1 );
			$blog->addPost( $userId, $_POST['postTitle'], $_POST['postText'] );
		}
		$this->view->redirect( '/adm/blogs/' );

	}

	public function deletepostAction( Param\NamedInt $id = null ) {

		Blogs\Post::delete( $id->val() );  
		$this->view->redirect( '/adm/blogs/' );

	}
	
	public function editpostAction( Param\NamedInt $id = null ) {
		
		$this->root->appendChild( $this->xml->createElement( 'blogs-editPost' ) );
		
		if( $post = Blogs\Post::getById( $id->val() ) ) {
			$Users = Difra\Plugins\Users::getInstance();
			$usersXml = $this->root->appendChild( $this->xml->createElement( 'users' ) );
			$Users->getListXML(  $usersXml );
			/** @var $editNode \DOMElement */
			$editNode = $this->root->appendChild( $this->xml->createElement( 'postData' ) );
			$post->getXML( $editNode, true );
			
		} else {
			$this->view->redirect( '/adm/blogs/' );
		}
	}
	
	public function updatepostAction( Param\NamedInt $id = null ) {
		
		if( $post = Blogs\Post::getById( $id ) ) {
			$post->setTitle( $_POST['postTitle'] );
			$post->setText( $_POST['postText'] );
			$post->setUser( $_POST['user'] );
			$post->save();
		}
		$this->view->redirect( '/adm/blogs/' );
	}
}

