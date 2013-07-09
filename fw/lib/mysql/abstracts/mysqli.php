<?php

namespace Difra\MySQL\Abstracts;

use Difra\Exception;

/**
 * Адаптер MySQLi
 * Class MySQLi
 *
 * @package Difra\MySQL
 */
class MySQLi extends Common {

	/**
	 * Возвращает доступность модуля
	 * @return bool
	 */
	static function isAvailable() {

		return extension_loaded( 'mysqli' );
	}

	/**
	 * Объект соединения
	 * @var \mysqli
	 */
	public $db = null;

	/**
	 * Реализация установки соединения с базой
	 * @throws \Difra\Exception
	 */
	protected function realConnect() {

		$this->db =
			@new \mysqli( !empty( $this->config['hostname'] ) ? 'p:' . $this->config['hostname'] : '', $this->config['username'], $this->config['password'] );
		if( $this->db->connect_error ) {
			throw new Exception( $this->error = $this->db->connect_error );
		}
		$this->db->set_charset( 'utf8' );
		if( !$this->db->select_db( $this->config['database'] ) ) {
			throw new Exception( $this->error = $this->db->error );
		}
	}

	/**
	 * Реализация запроса в базу
	 * @param string $query
	 * @throws \Difra\Exception
	 */
	protected function realQuery( $query ) {

		$this->db->query( $query );
		if( $err = $this->db->error and self::$errorReporting ) {
			throw new Exception( "MySQL error: [$err] on request [$query]" );
		}
	}

	/**
	 * Реализация получения данных из базы
	 * @param string $query
	 * @param bool   $replica
	 * @return array|mixed|null
	 * @throws \Difra\Exception
	 */
	protected function realFetch( $query, $replica = false ) {

		$res = $this->db->query( $query );
		if( $err = $this->db->error ) {
			if( self::$errorReporting ) {
				throw new Exception( 'MySQL: ' . $err );
			}
			return null;
		}
		if( $this->isND() ) {
			// при наличии mysqlnd
			return $res->fetch_all( MYSQLI_ASSOC );
		} else {
			// иначе собираем массив обычным методом
			$table = array();
			while( $row = $res->fetch_array( MYSQLI_ASSOC ) ) {
				$table[] = $row;
			}
		}
		return $table;
	}

	/**
	 * Реализация начала транзакции
	 */
	protected function transactionStart() {

		$this->db->autocommit( false );
	}

	/**
	 * Реализация окончания транзакции
	 */
	protected function transactionCommit() {

		$this->db->autocommit( true );
	}

	/**
	 * Реализация отмены транзакции
	 */
	protected function transactionCancel() {

		$this->db->rollback();
		$this->db->autocommit( true );
	}

	/**
	 * Реализация обезопасивания строки
	 * @param $string
	 * @return string
	 */
	protected function realEscape( $string ) {

		return $this->db->real_escape_string( $string );
	}

	/**
	 * Реализация last_insert_id()
	 *
	 * @return int
	 */
	public function getLastId() {

		return $this->db->insert_id;
	}

	/**
	 * Реализация affected_rows()
	 *
	 * @return int
	 */
	public function getAffectedRows() {

		return $this->db->affected_rows;
	}

}
