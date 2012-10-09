<?php

namespace Difra\MySQL\SQL;

class Table extends Common {

	private $name = '';
	private $definitions = array();
	private $goal = array();

	/**
	 * Создаёт новый объект таблицы
	 * @param array $chunks
	 *
	 * @return Table
	 */
	public static function create( $chunks = null ) {

		$table = new self;
		if( $chunks ) {
			$table->loadChunks( $chunks, false );
		}
		return $table;
	}

	/**
	 * Возвращает объект
	 * @param $name
	 *
	 * @return null
	 */
	public static function getByName( $name ) {

		return self::$list[$name] ? self::$list[$name] : null;
	}

	/**
	 * Загружает структуру таблиц из чанков
	 * @param array $chunks
	 * @param bool  $goal
	 *
	 * @throws \Difra\Exception
	 */
	public function loadChunks( $chunks, $goal = true ) {

		// пропускаем CREATE TABLE
		if( $chunks[0] != 'CREATE' or $chunks[1] != 'TABLE' ) {
			throw new \Difra\Exception( 'Expected to get CREATE TABLE chunks' );
		}
		array_shift( $chunks );
		array_shift( $chunks );
		// получаем имя таблицы
		$this->name              = self::chunk2name( array_shift( $chunks ) );
		self::$list[$this->name] = $this;
		// получаем строки с определениями столбцов и ключей
		$definitions = self::getDefinitions( $chunks );
		if( !$goal ) {
			$this->definitions = $definitions;
		} else {
			$this->goal = $definitions;
		}
	}

	/**
	 * Устанавливает Goal таблицы, имя которой содержится в чанках, либо создаёт новый объект таблицы
	 *
	 * @param $chunks
	 *
	 * @throws \Difra\Exception
	 */
	public static function autoGoal( $chunks ) {

		if( $chunks[0] != 'CREATE' or $chunks[1] != 'TABLE' ) {
			throw new \Difra\Exception( 'Expected to get CREATE TABLE chunks' );
		}
		if( $name = self::chunk2name( $chunks[2] ) ) {
			$o = self::getByName( $name );
		} else {
			$o = self::create();
		}
		$o->loadChunks( $chunks );
	}

	/**
	 * Получает определения столбцов и ключей из чанков
	 * @param $chunks
	 *
	 * @return array
	 * @throws \Difra\Exception
	 */
	private static function getDefinitions( &$chunks ) {

		if( $chunks[0] != '(' ) {
			throw new \Difra\Exception( 'Expected \'(\' after CREATE TABLE `...`' );
		}
		array_shift( $chunks );
		$lines = array();
		$line  = array();
		$d     = 0;
		while( !empty( $chunks ) ) {
			$a = array_shift( $chunks );
			if( $a == '(' ) {
				$d++;
			} elseif( $d == 0 and $a == ')' ) {
				if( !empty( $line ) ) {
					$lines[] = $line;
					$line    = array();
				}
				break;
			} elseif( $a == ')' ) {
				$d--;
			} elseif( $d == 0 and $a == ',' ) {
				if( !empty( $line ) ) {
					$lines[] = $line;
					$line    = array();
				}
				continue;
			}
			$line[] = $a;
		}
		if( !empty( $line ) or empty( $lines ) ) {
			throw new \Difra\Exception( 'Definitions parse error' );
		}
		return $lines;
	}

	/**
	 * @param \DOMElement $node
	 */
	public function getStatusXML( $node ) {

		/** @var $statusNode \DOMElement */
		$statusNode = $node->appendChild( $node->ownerDocument->createElement( 'table' ) );
		$statusNode->setAttribute( 'name', $this->name );
		$current = array();
		if( !empty( $this->definitions ) ) {
			foreach( $this->definitions as $def ) {
				$current[] = \Difra\MySQL\Parser::def2string( $def );
			}
		}
		$goal = array();
		if( !empty( $this->goal ) ) {
			foreach( $this->goal as $g ) {
				$goal[] = \Difra\MySQL\Parser::def2string( $g );
			}
		}
		$diff = \Difra\Libs\Diff::diffArrays( $current, $goal );
		if( is_array( $diff ) and !empty( $diff ) ) {
			foreach( $diff as $d ) {
				/** @var $diffNode \DOMElement */
				$diffNode = $statusNode->appendChild( $node->ownerDocument->createElement( 'diff' ) );
				@$diffNode->setAttribute( 'sign', $d['sign'] );
				@$diffNode->setAttribute( 'value', $d['value'] );
			}
		}
	}
}