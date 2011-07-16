<?php

namespace Difra;

class MySQL {

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
		$this->db = new \mysqli( 'p:' . ( isset( $this->config['hostname'] ) ? $this->config['hostname'] : 'localhost' ),
			$this->config['username'], $this->config['password'], $this->config['database'] );
		if( $this->db->connect_errno ) {
			throw new Exception( 'Can\'t connect to MySQL: ' . $this->db->connect_error );
		}
		$this->connected = true;
		$this->db->set_charset( 'utf8' );
	}

	public function query( $qstring ) {

		if( !is_array( $qstring ) ) {
			$this->connect();
			$this->db->query( $qstring );
			$this->queries++;
			Debugger::getInstance()->addLine( "MySQL: " . $qstring );
			if( $this->connect->errno ) {
				throw new exception( 'MySQL error: ' . $this->db->error );
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
				throw new exception( 'MySQL transaction failed because of ' . $ex->getMessage() );
			}
		}
	}

	public function fetch( $query ) {

		$this->connect();
		$table = array();
		$result = $this->db->query( $query );
		$this->queries++;
		Debugger::getInstance()->addLine( "MySQL: " . $query );
		if( $err = $this->db->error ) {
			throw new exception( 'MySQL: ' . $err );
		}
		// XXX: вместо этого, по идее, надо делать fetch_all, но оно требует mysqlnd
		while( $row = $result->fetch_array( MYSQLI_ASSOC ) ) {
			$table[] = $row;
		}
		return $table;
	}

	public function fetchWithId( $query ) {

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
		return $this->db->real_escape_string( $string );
	}

	public function getLastId() {

		$id = $this->db->insert_id;
		return $id ? $id : null;
	}
}
