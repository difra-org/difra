<?php

namespace Difra;

class MySQL {

	private $config = null;
	public $connected = false;
	/**
	 * @var \mysqli
	 */
	public $db = null;
	public $queries = 0;

	/**
	 * Конструктор
	 * @param bool $new        Создать новое соединение с базой
	 */
	public function __construct( $new = false ) {

		$this->config = Config::getInstance()->get( 'db' );
		if( !$this->config ) {
			$this->config = array(
				'username' => '',
				'password' => '',
				'database' => ''
			);
		}
	}

	/**
	 * Синглтон
	 * @static
	 *
	 * @param bool $new        Создать новое соединение с базой
	 *
	 * @return MySQL
	 */
	static public function getInstance( $new = false ) {

		static $_self = null;
		return ( $_self and !$new ) ? $_self : $_self = new self( $new );
	}

	/**
	 * Устанавливает соединение с базой
	 * @throws Exception
	 * @return void
	 */
	private function connect() {

		if( $this->connected ) {
			return;
		}
		try {
			$this->db = @new \mysqli( 'p:' . ( isset( $this->config['hostname'] ) ?
								     $this->config['hostname']
								     : 'localhost' ),
						  $this->config['username'], $this->config['password'], $this->config['database'] );
			if( $this->db->connect_errno ) {
				throw new Exception( 'Can\'t connect to MySQL: ' . $this->db->connect_error );
			}
		} catch( Exception $e ) {
			View::getInstance()->httpError( 500 );
		}
		$this->connected = true;
		$this->db->set_charset( 'utf8' );
	}

	/**
	 * Сделать запрос в базу
	 * @throws exception
	 *
	 * @param string|array $query        SQL-запрос
	 *
	 * @return void
	 */
	public function query( $query ) {

		if( !is_array( $query ) ) {
			$this->connect();
			$this->db->query( $query );
			$this->queries++;
			Debugger::getInstance()->addDBLine( 'MySQL', $query );
			if( $err = $this->db->error and !Site::isInit() ) {
				throw new Exception( "MySQL error: [$err] on request [$query]" );
			}
		} else {
			try {
				$this->db->autocommit( false );
				foreach( $query as $subQuery ) {
					$this->query( $subQuery );
				}
				$this->db->autocommit( true );
			} catch( Exception $ex ) {
				$this->db->rollback();
				$this->db->autocommit( true );
				if( !Site::isInit() ) {
					throw new Exception( 'MySQL transaction failed because of ' . $ex->getMessage() );
				}
			}
		}
	}

	/**
	 * Возвращает результат запроса
	 * @throws exception
	 *
	 * @param string $query          SQL-запрос
	 * @param bool   $replica        Позволить читать данные из реплики
	 *
	 * @return array
	 */
	public function fetch( $query, $replica = false ) {

		$this->connect();
		$table  = array();
		$result = $this->db->query( $query );
		$this->queries++;
		Debugger::getInstance()->addDBLine( 'MySQL', $query );
		if( $err = $this->db->error ) {
			if( !Site::isInit() ) {
				throw new \Difra\Exception( 'MySQL: ' . $err );
			}
			return null;
		}
		// XXX: вместо этого, по идее, надо делать fetch_all, но оно требует mysqlnd
		while( $row = $result->fetch_array( MYSQLI_ASSOC ) ) {
			$table[] = $row;
		}
		return $table;
	}

	/**
	 * Возвращает результаты запроса в ассоциативном массиве id => row
	 *
	 * @throws exception
	 *
	 * @param string $query          SQL-запрос
	 * @param bool   $replica        Позволить читать данные из реплики
	 *
	 * @return array
	 */
	public function fetchWithId( $query, $replica = false ) {

		$this->connect();
		$table  = array();
		$result = $this->db->query( $query );
		$this->queries++;
		Debugger::getInstance()->addDBLine( 'MySQL', $query );
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
	 * @param string $query          SQL-запрос
	 * @param bool   $replica        Позволить читать данные из реплики
	 *
	 * @return array|bool
	 */
	public function fetchRow( $query, $replica = false ) {

		$data = $this->fetch( $query, $replica );
		return isset( $data[0] ) ? $data[0] : false;
	}

	/**
	 * Возвращает одно значение из результатов запроса
	 * @param string $query          SQL-запрос
	 * @param bool   $replica        Позволить читать данные из реплики
	 *
	 * @return mixed|null
	 */
	public function fetchOne( $query, $replica = false ) {

		$data = $this->fetchRow( $query, $replica );
		return !empty( $data ) ? array_shift( $data ) : null;
	}

	/**
	 * Возвращает результат SQL-запроса в виде дерева XML
	 *
	 * @param \DOMNode $node           XML-Нода
	 * @param string   $query          Запрос
	 * @param bool     $replica        Позволить читать данные из реплики
	 *
	 * @return bool
	 */
	public function fetchXML( $node, $query, $replica = false ) {

		$data = $this->fetch( $query, $replica );
		if( empty( $data ) ) {
			return false;
		}
		foreach( $data as $row ) {
			$subnode = $node->appendChild( $node->ownerDocument->createElement( 'item' ) );
			$this->getRowAsXML( $subnode, $row );
		}
		return true;
	}

	/**
	 * Возвращает строку из базы данных в XML
	 *
	 * @param \DOMElement $node
	 * @param string      $query
	 * @param bool        $replica
	 *
	 * @return bool
	 */
	public function fetchRowXML( $node, $query, $replica = false ) {

		$row = $this->fetchRow( $query, $replica );
		return $this->getRowAsXML( $node, $row );
	}

	/**
	 * Берет значения из массива и возвращает их в виде дерева XML
	 *
	 * @param $node
	 * @param $row
	 *
	 * @return bool
	 */
	private function getRowAsXML( $node, $row ) {

		if( empty( $row ) ) {
			return false;
		}
		foreach( $row as $k => $v ) {
			if( trim( $v ) and preg_match( '/^(i|s|a|o|d)(.*);/si', $v ) ) { // serialize!
				$arr     = @unserialize( $v );
				$subnode = $node->appendChild( $node->ownerDocument->createElement( $k ) );
				$this->getRowAsXML( $subnode, $arr );
			} else {
				$node->setAttribute( $k, $v );
			}
		}
		return true;
	}

	/**
	 * Безопасно «обернуть» строку для SQL-запроса
	 *
	 * @param string $string        Строка
	 *
	 * @return string
	 */
	public function escape( $string ) {

		$this->connect();
		return $this->db->real_escape_string( $string );
	}

	/**
	 * Возвращает last_insert_id()
	 *
	 * @return int
	 */
	public function getLastId() {

		return $this->db->insert_id;
	}

	/**
	 * Возвращает affected_rows()
	 *
	 * @return int
	 */
	public function getAffectedRows() {

		return $this->db->affected_rows;
	}

	/**
	 * Возвращает found_rows()
	 *
	 * @return int
	 */
	public function getFoundRows() {

		return $this->fetchOne( "SELECT FOUND_ROWS()" );
	}
}
