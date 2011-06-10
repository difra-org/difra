<?php

final class MySQL {

	private $config = null;
	public $connected = false;
	public $id = null;
	public $db = null;
	public $queries = 0;

	public function __construct( $reset = false ) {

		$this->config = Site::getInstance( $reset )->getDbConfig();
	}

	static public function getInstance( $reset = false ) {

		static $_self = null;
		return ( $_self and !$reset ) ? $_self : $_self = new self( $reset );
	}

	private function connect() {

		if( $this->connected ) {
			return;
		}
		$this->db = @mysql_pconnect( isset( $this->config['hostname'] ) ? $this->config['hostname'] : 'localhost',
			$this->config['username'], $this->config['password'] );
		if( !$this->db ) {
			throw new exception( "Can't connect to MySQL server." );
		}
		if( !mysql_select_db( $this->config['database'], $this->db ) ) {
			throw new exception( "Can't select MySQL database [{$this->config['database']}]." );
		}
		$this->connected = true;
		$this->query( "SET names UTF8" );
	}

	public function query( $qstring ) {

		$this->connect();
		mysql_query( $qstring, $this->db );
		$this->queries++;
		Debugger::getInstance()->addLine( "MySQL: " . $qstring );
		if( $err = mysql_error( $this->db ) ) {
			throw new exception( 'MySQL error: ' . $err );
		}
	}

	public function fetch( $query ) {

		$this->connect();
		$table = array();
		$result = mysql_query( $query, $this->db );
		$this->queries++;
		Debugger::getInstance()->addLine( $query );
		if( $err = mysql_error( $this->db ) ) {
			throw new exception( 'MySQL: ' . $err );
		}
		while( $row = mysql_fetch_array( $result, MYSQL_ASSOC ) ) {
			$table[] = $row;
		}
		return $table;
	}

	public function fetchRow( $query ) {

		$data = $this->fetch( $query );
		return isset( $data[0] ) ? $data[0] : false;
	}
	
	public function fetchOne( $query ) {
		
		$data = $this->fetchRow( $query );
		return !empty( $data ) ? array_shift( $data ) : null;
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

		$this->connect();
		return mysql_real_escape_string( $string, $this->db );
	}

	public function getLastId() {

		$id = $this->fetch( 'SELECT LAST_INSERT_ID()' );
		return isset( $id[0]['LAST_INSERT_ID()'] ) ? $id[0]['LAST_INSERT_ID()'] : null;
	}
}
