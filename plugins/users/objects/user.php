<?php

namespace Difra\Plugins\Users\Objects;

class User extends \Difra\Unify {

	static protected $tableName = 'users';
	static protected $propertiesList = array(

		'id' => array(
			'type' => 'int',
			'primary' => true,
			'options' => 'auto_increment'
		),
		'email' => array(
			'type' => 'char',
			'length' => 250,
			'unique' => true,
			'required' => true
		),
		'password' => array(
			'type' => 'char',
			'length' => 32,
			'required' => true,
			'autoload' => false
		),
		'active' => array(
			'type' => 'tinyint',
			'length' => 1,
			'index' => true,
			'default' => 0,
			'required' => true
		),
		'banned' => array(
			'type' => 'tinyint',
			'length' => 1,
			'index' => true,
			'default' => 0,
			'required' => true
		),
		'moderator' => array(
			'type' => 'tinyint',
			'length' => 1,
			'index' => true,
			'default' => 0,
			'required' => true
		),
		'activation' => array(
			'type' => 'char',
			'length' => 24,
			'index' => true,
			'autoload' => false
		),
		'registered' => array(
			'type' => 'timestamp',
			'default' => 'CURRENT_TIMESTAMP'
		),
		'logged' => array(
			'type' => 'timestamp',
			'default' => '0000-00-00 00:00:00'
		),
		'passwordChanged' => array(
			'type' => 'timestamp'
		)
	);

	static protected $defaultOrderDesc = 'registered';
	static protected $defaultOrder = 'registered';

	protected function postProcessXML( $node ) {

		$Locale = \Difra\Locales::getInstance();
		$node->setAttribute( 'registered', $Locale->getDateFromMysql( $this->registered, true ) );

		if( $this->logged == '0000-00-00 00:00:00' ) {
			$loggedDate = null;
		} else {
			$loggedDate = $Locale->getDateFromMysql( $this->logged, true );
		}
		$node->setAttribute( 'logged', $loggedDate );
	}

}