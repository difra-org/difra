<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\MySQL\Abstracts;

/**
 * Class None
 *
 * @package Difra\MySQL
 */
class None extends Common {

	protected function realConnect() {

		$this->connected = false;
	}

	/**
	 * Заглушка для realQuery
	 *
	 * @param string $query
	 */
	protected function realQuery( $query ) {
	}

	/**
	 * Заглушка для realFetch
	 *
	 * @param string $query
	 * @param bool   $replica
	 *
	 * @return array|null
	 */
	protected function realFetch( $query, $replica = false ) {

		return null;
	}

	/**
	 * Заглушка для getAffectedRows
	 *
	 * @return int
	 */
	protected function getAffectedRows() {

		return 0;
	}

	/**
	 * Заглушка для getLastId
	 *
	 * @return int
	 */
	protected function getLastId() {

		return 0;
	}

	/**
	 * Заглушка для realEscape
	 *
	 * @param $string
	 *
	 * @return string
	 */
	protected function realEscape( $string ) {

		return $string;
	}
}