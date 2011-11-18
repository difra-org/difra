<?php

namespace Difra;

class MySQL {

	private $config = null;
	public $connected = false;
	public $db = null;
	public $queries = 0;

	/**
	 * Конструктор
	 * @param bool $reset	Создать новое соединение с базой
	 */
	public function __construct( $reset = false ) {

		$this->config = Site::getInstance( $reset )->getDbConfig();
	}

	/**
	 * Синглтон
	 * @static
	 * @param bool $reset	Создать новое соединение с базой
	 * @return MySQL
	 */
	static public function getInstance( $reset = false ) {

		static $_self = null;
		return ( $_self and !$reset ) ? $_self : $_self = new self( $reset );
	}

	/**
	 * Устанавливает соединение с базой
	 * @throws Exception
	 * @return
	 */
	private function connect() {

		if( $this->connected ) {
			return;
		}
		$this->db = new \mysqli( 'p:' . ( isset( $this->config['hostname'] ) ? $this->config['hostname'] : 'localhost' ),
			$this->config['username'], $this->config['password'], $this->config['database'] );
		if( $this->db->connect_errno ) {
			throw new Exception( 'Can\'t connect to MySQL: ' . $this->db->connect_error );
		}
		$this->connected = true;
		$this->db->set_charset( 'utf8' );
	}

	/**
	 * Сделать запрос в базу
	 * @throws exception
	 * @param string $qstring	SQL-запрос
	 * @return void
	 */
	public function query( $qstring ) {

		if( !is_array( $qstring ) ) {
			$this->connect();
			$this->db->query( $qstring );
			$this->queries++;
			Debugger::getInstance()->addLine( "MySQL: " . $qstring );
			if( $err = $this->db->error ) {
				throw new Exception( 'MySQL error: ' . $err );
			}
		} else {
			try {
				$this->db->autocommit( false );
				foreach( $qstring as $subQuery ) {
					$this->query( $subQuery );
				}
				$this->db->autocommit( true );
			} catch( Exception $ex ) {
				$this->db->rollback();
				$this->db->autocommit( true );
				throw new Exception( 'MySQL transaction failed because of ' . $ex->getMessage() );
			}
		}
	}

	/**
	 * Возвращает результат запроса
	 * @throws exception
	 * @param string $query	SQL-запрос
	 * @param bool $replica	Позволить читать данные из реплики
	 * @return array
	 */
	public function fetch( $query, $replica = false ) {

		$this->connect();
		$table = array();
		$result = $this->db->query( $query );
		$this->queries++;
		Debugger::getInstance()->addLine( 'MySQL: ' . $query );
		if( $err = $this->db->error ) {
			throw new Exception( 'MySQL: ' . $err );
		}
		// XXX: вместо этого, по идее, надо делать fetch_all, но оно требует mysqlnd
		while( $row = $result->fetch_array( MYSQLI_ASSOC ) ) {
			$table[] = $row;
		}
		return $table;
	}

	/**
	 * Возвращает результаты запроса в ассоциативном массиве id => row
	 * @throws exception
	 * @param string $query	SQL-запрос
	 * @param bool $replica	Позволить читать данные из реплики
	 * @return array
	 */
	public function fetchWithId( $query, $replica = false ) {

		$this->connect();
		$table = array();
		$result = $this->db->query( $query );
		$this->queries++;
		Debugger::getInstance()->addLine( "MySQL: " . $query );
		if( $err = $this->db->error ) {
			throw new exception( 'MySQL: ' . $err );
		}
		while( $row = $result->fetch_array( MYSQLI_ASSOC ) ) {
			$table[$row['id']] = $row;
		}
		return $table;
	}

	/**
	 * Возвращает одну строку результатов запроса
	 * @param string $query	SQL-запрос
	 * @param bool $replica	Позволить читать данные из реплики
	 * @return array|bool
	 */
	public function fetchRow( $query, $replica = false ) {

		$data = $this->fetch( $query, $replica );
		return isset( $data[0] ) ? $data[0] : false;
	}

	/**
	 * Возвращает одно значение из результатов запроса
	 * @param string $query	SQL-запрос
	 * @param bool $replica	Позволить читать данные из реплики
	 * @return mixed|null
	 */
	public function fetchOne( $query, $replica = false ) {
		
		$data = $this->fetchRow( $query, $replica );
		return !empty( $data ) ? array_shift( $data ) : null;
	}

	/**
	 * Возвращает результат SQL-запроса в виде дерева XML
	 * @param DOMNode $node		XML-Нода
	 * @param string $query		Запрос
	 * @param bool $replica		Позволить читать данные из реплики
	 * @return bool
	 */
	public function fetchXML( $node, $query, $replica = false ) {

		$data = $this->fetch( $query, $replica );
		if( empty( $data ) ) {
			return false;
		}
		foreach( $data as $row ) {
			$subnode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
			foreach( $row as $k => $v ) { 
				if( trim( $v ) and preg_match( '/^(i|s|a|o|d)(.*);/si', $v ) ) { // serialize!
					$arr = unserialize( $v );
					$subnode2 = $subnode->appendChild( $node->ownerDocument->createElement( $k ) );
					foreach( $arr as $k2 => $v2 ) {
						$subnode2->setAttribute( $k2, $v2 );
					}
				} else {
					$subnode->setAttribute( $k, $v );
				}
			}
		}
		return true;
	}

	/**
	 * Безопасно «обернуть» строку для SQL-запроса
	 * @param string $string	Строка
	 * @return string
	 */
	public function escape( $string ) {

		$this->connect();
		return $this->db->real_escape_string( $string );
	}

	/**
	 * Возвращает last_insert_id()
	 * @return int
	 */
	public function getLastId() {

		return $this->db->insert_id;
	}

	/**
	 * Возвращает affected_rows()
	 * @return int
	 */
	public function getAffectedRows() {

		return $this->db->affected_rows;
	}

	/**
	 * Возвращает found_rows()
	 * @return int
	 */
	public function getFoundRows() {

		return $this->fetchOne( "SELECT FOUND_ROWS()" );
	}
}
