<?php

namespace {
	
	class MySQL extends Difra\MySQL {}
	class Site {
		public  static function getInstance() {
			return Difra\Site::getInstance();
		}
	}
	class Locales extends Difra\Locales {}
	class Plugger extends Difra\Plugger {}
	class Auth extends Difra\Auth {}

}