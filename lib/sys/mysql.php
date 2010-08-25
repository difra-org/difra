<?php

require_once ( dirname( __FILE__ ) . '/site.php' );
require_once ( dirname( __FILE__ ) . '/common.php' );

/**
 * Работа с MySQL
 *
 */
final class MySQL {

	private $config = null;
	public $connected = false;
	public $id = null;
	public $db = null;
	public $queries = 0;
	public $queriesList = array();

	/**
	 * Конструктор
	 *
	 */
	public function __construct( $reset = false ) {

		$this->config = Site::getInstance( $reset )->getDbConfig();
	}

	/**
	 * Синглтон
	 *
	 * @return MySQL
	 */
	static public function getInstance( $reset = false ) {

		static $_self = null;
		return ( $_self and !$reset ) ? $_self : $_self = new self( $reset );
	}

	/**
	 * Устанавливает соединение с сервером или повторно его использует, а также выбирает нужную базу.
	 *
	 * @return boolean
	 */
	private function connect() {

		if( !$this->connected ) {
			$this->db = @mysql_pconnect( isset( $this->config['hostname'] ) ? $this->config['hostname'] : 'localhost',
				$this->config['username'], $this->config['password'] );
			if( $this->db ) {
				if( mysql_select_db( $this->config['database'], $this->db ) ) {
					$this->connected = true;
					$this->query( "SET names UTF8" );
				} else {
					error( "Instance [{$this->id}] can't select database [{$this->config['database']}].", __LINE__,
						__FILE__ );
				}
			} else {
				error( "Instance [{$this->id}] can't connect to database server.", __FILE__, __LINE__ );
			}
		}
		return $this->connected;
	}

	/**
	 * Совершает запрос в базу
	 *
	 * @param string $qstring
	 * @return boolean
	 */
	public function query( $qstring ) {

		if( $this->connect() ) {
			mysql_query( $qstring, $this->db );
			$this->queries++;
			$this->queriesList[] = $qstring;
			if( !( $err = mysql_error( $this->db ) ) ) {
				return true;
			} else {
				error( 'MySQL: ' . $err, __FILE__, __LINE__ );
			}
		}
		return false;
	}

	/**
	 * Производит выборку из базы, возвращает массив строк
	 *
	 * @param string $query
	 * @return array
	 */
	public function fetch( $query ) {

		if( $this->connect() ) {
			$table = array();
			$result = mysql_query( $query, $this->db );
			$this->queries++;
			$this->queriesList[] = $query;
			if( mysql_error( $this->db ) ) {
				return false;
			}
			while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
				$table[] = $row;
			}
			if( !( $err = mysql_error( $this->db ) ) ) {
				return $table;
			} else {
				error( 'MySQL: ' . $err, __FILE__, __LINE__ );
			}
		}
		return false;
	}

	public function fetchLine( $query ) {

		$data = $this->fetch( $query );
		return isset( $data[0] ) ? $data[0] : false;
	}

	/**
	 * Безопасно «оборачивает» строку, экранируя небезопасные символы
	 *
	 * @param string $string
	 * @return string
	 */
	public function escape( $string ) {

		if( $this->connect() ) {
			return mysql_real_escape_string( $string, $this->db );
		} else {
			return false;
		}
	}

	/**
	 * Возвращает id последней добавленной в таблицу строки
	 *
	 * @return integer
	 */
	public function getLastId() {

		$id = $this->fetch( 'SELECT LAST_INSERT_ID()' );
		return isset( $id[0]['LAST_INSERT_ID()'] ) ? $id[0]['LAST_INSERT_ID()'] : false;
	}
}
