<?php

namespace Difra\Plugins\Users;
use Difra\Unify\Storage;

class Additionals {

	/**
	 * Возвращает дополнительные поля пользователя
	 * @param $id
	 */
	public static function getAdditionalsById( $id ) {

		$Search = new \Difra\Unify\Search( 'UsersFields' );
		$Search->addConditions( array( 'id' => $id ) );
		$objects = $Search->doQuery();

		$returnArray = null;

		if( !is_null( $objects ) ) {
			foreach( $objects as $k=>$object ) {
				$returnArray[ $object->name ] = $object->value;
			}
		}

		return $returnArray;
	}

	/**
	 * Сохраняет дополнительные поля пользователя
	 * @param       $userId
	 * @param array $fields(names, values)
	 */
	public static function save( $userId, array $fields ) {

		$db = \Difra\MySQL::getInstance();
		$db->query( "DELETE FROM `users_fields` WHERE `id`='" . intval( $userId ) . "'" );

		if( !isset( $fields['names'] ) || !isset( $fields['values'] ) ) {
			return;
		}

		$Class = Storage::getClass( 'UsersFields' );
		foreach( $fields['names'] as $k=>$fieldName ) {

			if( isset( $fields['values'][$k] ) && $fields['values'][$k] != '' ) {
				$object = $Class::create();
				$object->id = $userId;
				$object->name = $fieldName;
				$object->value = $fields['values'][$k];
			}
		}
	}

}