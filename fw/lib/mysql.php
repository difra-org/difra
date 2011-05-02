<?php

//require_once ( dirname( __FILE__ ) . '/site.php' );
//require_once ( dirname( __FILE__ ) . '/common.php' );

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

	public function __construct( $reset = false ) {

		$this->config = Site::getInstance( $reset )->getDbConfig();
	}

	static public function getInstance( $reset = false ) {

		static $_self = null;
		return ( $_self and !$reset ) ? $_self : $_self = new self( $reset );
	}

	private function connect() {

		if( !$this->connected ) {
			$this->db = @mysql_pconnect( isset( $this->config['hostname'] ) ? $this->config['hostname'] : 'localhost',
				$this->config['username'], $this->config['password'] );
			if( !$this->db ) {
				throw new exception( "Can't connect to MySQL server." );
			}
			if( mysql_select_db( $this->config['database'], $this->db ) ) {
				$this->connected = true;
				$this->query( "SET names UTF8" );
				$this->queries = 0;
				$this->queriesList = array();
			} else {
				throw new exception( "Can't select MySQL database [{$this->config['database']}]." );
			}
		}
		return $this->connected;
	}

	public function query( $qstring ) {

		if( $this->connect() ) {
			mysql_query( $qstring, $this->db );
			$this->queries++;
			$this->queriesList[] = $qstring;
			if( !( $err = mysql_error( $this->db ) ) ) {
				return true;
			} else {
				throw new exception( 'MySQL: ' . $err );
			}
		}
		return false;
	}

	public function fetch( $query ) {

		if( !$this->connect() ) {
			return false;
		}
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
			throw new exception( 'MySQL: ' . $err );
		}
	}

	public function fetchRow( $query ) {

		$data = $this->fetch( $query );
		return isset( $data[0] ) ? $data[0] : false;
	}

	public function fetchXML( $node, $query ) {

		$data = $this->fetch( $query );
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

	public function escape( $string ) {

		if( !$this->connect() ) {
			return false;
		}
		return mysql_real_escape_string( $string, $this->db );
	}

	public function getLastId() {

		$id = $this->fetch( 'SELECT LAST_INSERT_ID()' );
		return isset( $id[0]['LAST_INSERT_ID()'] ) ? $id[0]['LAST_INSERT_ID()'] : false;
	}
}
