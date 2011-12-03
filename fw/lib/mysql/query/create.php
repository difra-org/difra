<?php

namespace Difra\MySQL\Query;

class Create extends \Difra\MySQL\Query {

	private $flags = array();
	private $table = '';
	private $definitions = null;
	private $options = array();

	public function __construct( $fragments ) {

		$this->fragments = $fragments;
		// CREATE
		if( mb_strtoupper( $fragments[0] ) != 'CREATE' ) {
			throw new \Difra\Exception( 'Create called without CREATE keyword. WTF?' );
		}
		array_shift( $fragments );
		// [TEMPORARY]
		if( mb_strtoupper( $fragments[0] ) == 'TEMPORARY' ) {
			$this->flags['Temporary'] = true;
			array_shift( $fragments );
		}
		// TABLE
		if( mb_strtoupper( $fragments[0] != 'TABLE' ) ) {
			throw new \Difra\Exception( 'CREATE ' . $fragments[0] . ' is not supported yet.<br/>' );
		}
		array_shift( $fragments );
		// [IF NOT EXISTS]
		if( mb_strtoupper( @implode( ' ', array_slice( $fragments, 0, 3 ) ) ) == 'IF NOT EXISTS' ) {
			$this->flags['IfNotExists'] = true;
			$fragments = array_slice( $fragments, 3 );
		}
		// tbl_name
		$this->table = $this->unquote( array_shift( $fragments ) );
		// (create definition, ...)
		// XXX: ещё бывают create ... from select, тогда дефайнишины опциональны, а также create ... like old_tbl_name
		// если так, то покачто лапки к верху — ещё не умеем так
		if( is_array( $fragments[0] ) ) {
			$defs = $this->parseDefinitions( $fragments[0] );
			$this->definitions = $this->formDefinitions( $defs );
		} else {
			throw new \Difra\Exception( 'CREATE TABLE without columns definitions is not supported yet.' );
		}
	}

	private function parseDefinitions( $fragments ) {

		$defs = array();
		$i = 0;
		$size = sizeof( $fragments );
		$next = array();
		while( $i < $size ) {
			$a = $fragments[$i];
			$i++;
			if( !is_array( $a ) and $a == ',' ) {
				if( !empty( $next ) ) {
					$defs[] = $next;
					$next = array();
				}
				continue;
			}
			$next[] = $a;
		}
		if( !empty( $next ) ) {
			$defs[] = $next;
		}
		return $defs;
	}

	private function formDefinitions( $defs ) {

		$newDefs = array();
		foreach( $defs as $def ) {
			switch( $t = strtoupper( $def[0] ) ) {
			case 'PRIMARY':
				$type = $t;
				$name = '';
				break;
			case 'UNIQUE':
				$type = $t;
				$name = $def[2];
				break;
			case 'FULLTEXT':
			case 'SPATIAL':
				$type = $t;
				switch( strtoupper( $def[1] ) ) {
				case 'INDEX':
				case 'KEY':
					$name = $def[2];
					break;
				default:
					$name = $def[1];
				}
				break;
			case 'KEY':
			case 'CONSTRAINT':
				$type = $t;
				$name = $def[1];
				break;
			default:
				$type = 'DATA';
				$name = $def[0];
			}
			$name = $this->unquote( $name );
			$newDefs[] = array(
				'type' => $type,
				'name' => $name,
				'defs' => $def,
				'text' => $this->toString( $def )
			);
		}
	}
}