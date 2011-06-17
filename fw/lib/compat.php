<?php

namespace {
	
	class MySQL {
		public static function getInstance() {
			return Difra\MySQL::getInstance();
		}
	}
	class Site {
		public static function getInstance() {
			return Difra\Site::getInstance();
		}
	}
	class Locales extends Difra\Locales {}
	class Plugger extends Difra\Plugger {}
	class Auth extends Difra\Auth {}
	class Cookies extends Difra\Cookies {}
}