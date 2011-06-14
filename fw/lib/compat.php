<?php

namespace {
	
	class MySQL extends Difra\MySQL {}
	class Site {
		public  static function getInstance() {
			return Difra\Site::getInstance();
		}
	}
	class Locales extends Difra\Locales {}
	class Resourcer extends Difra\Resourcer {}
	class Plugger extends Difra\Plugger {}
	class Mailer extends Difra\Mailer {}
	class Cache extends Difra\Cache {}
	class Auth extends Difra\Auth {}

}