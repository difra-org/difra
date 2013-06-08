<?php

namespace Difra;

class Envi {

	/** @var string Режим работы (web, cli, include) */
	static protected $mode = 'include';

	/** Установить режим работы */
	public static function setMode( $mode ) {

		self::$mode = $mode;
	}

	/** Получить режим работы */
	public static function getMode() {

		return self::$mode;
	}
}