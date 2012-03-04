<?php

namespace Difra\MySQL;

class Updater {

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function check() {

		$goal = $this->collectGoal();
		return $this->compare( $goal );
	}

	private function collectGoal() {

		$paths = \Difra\Plugger::getInstance()->getPaths();
		$tables = array();
		foreach( $paths as $path ) {
			if( is_readable( $path . '/bin/db.sql' ) ) {
				$tables[] = file_get_contents( $path . '/bin/db.sql' );
			}
		}
		return $this->parseDump( implode( "\n", $tables ) );
	}

	private function parseDump( $dump ) {

		$fragments = $this->chop( $dump );
		return $this->getQueries( $fragments );
	}

	private function chop( $text, $recursive = false ) {

		$shards = array();
		$next = '';
		$i = 0;
		$size = mb_strlen( $text );
		$comm = $linecomm = false;
		$str = '';
		while( $i < $size ) {
			$a = mb_substr( $text, $i, 1 );
			$a1 = ( $i < $size - 1 ) ? mb_substr( $text, $i + 1, 1 ) : '';
			$i++;

			/* Строки */
			// проверка на конец строки
			if( $str !== '' ) {
				$str .= $a;
				if( mb_substr( $str, 0, 1 ) == $a and mb_substr( $str, -1 ) != '\\' ) {
					$shards[] = $str;
					$str = '';
				}
				continue;
			}
			// проверка на начало строки
			if( !$comm ) {
				if( $a == '"' or $a == "'" ) {
					if( $next !== '' ) {
						$shards[] = $next;
						$next = '';
					}
					$str = $a;
				}
			}

			/* Комментарии */
			// проверка на конец многострочного комментария
			if( $comm ) {
				if( $a == '*' and $a1 == '/' ) {
					$comm = false;
					$i++;
				}
				continue;
			}
			// проверка на начало многострочного комментария ('/* ... */')
			if( $a == '/' and $a1 == '*' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next = '';
				}
				$comm = true;
				$i++;
				continue;
			}
			// проверка на конец строкового комментария
			if( $linecomm ) {
				if( $a == "\n" ) {
					$linecomm = false;
				}
				continue;
			}
			// проверка на начало строкового комментария ('-- comment')
			if( $a == '-' and $a1 == '-' and $this->isSpace( mb_substr( $text, $i + 1, 1 ) ) ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next = '';
				}
				$linecomm = true;
				$i += 2;
				continue;
			}
			// проверка на начало строкового комментария ('# comment')
			if( $a == '#' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next = '';
				}
				$linecomm = true;
				continue;
			}

			if( $a == '(' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next = '';
				}
				$res = $this->chop( mb_substr( $text, $i ), true );
				$shards[] = $res['data'];
				$i += $res['parsed'];
				continue;
			}

			if( $recursive and $a == ')' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					//$next = '';
				}
				return array( 'data' => $shards, 'parsed' => $i );
			}

			if( $a == ',' or $a == ';' or $a == '=' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next = '';
				}
				$shards[] = $a;
				continue;
			} elseif( !$this->isSpace( $a ) ) {
				$next .= $a;
				continue;
			} else {
				if( $next !== '' ) {
					$shards[] = $next;
					$next = '';
					continue;
				}
			}
		}
		if( $next !== '' ) {
			$shards[] = $next;
		}
		return $shards;
	}

	private function isSpace( $c ) {

		switch( $c ) {
		case "\n":
		case "\r":
		case "\t":
		case ' ':
			return true;
		}
		return false;
	}

	private function getQueries( $fragments ) {

		$size = sizeof( $fragments );
		$i = 0;
		$next = array();
		$queries = array();
		while( $i < $size ) {
			$a = $fragments[$i];
			$i++;
			if( $a == ';' ) {
				if( !empty( $next ) ) {
					$queries[] = \Difra\MySQL\Query::factory( $next );
					$next = array();
				}
				continue;
			}
			$next[] = $a;
		}
		if( !empty( $next ) ) {
			$queries[] = \Difra\MySQL\Query::factory( $next );
		}
		return $queries;
	}

	public function compare( $queries ) {

		$db = \Difra\MySQL::getInstance();
		$res = '';
		$tables = array();
		foreach( $queries as $query ) {
			if( !$query instanceof \Difra\MySQL\Query\Create ) {
				continue;
			}
			$tables[] = $table = $query->getTable();
			try {
				$currentStr = $db->fetchRow( 'SHOW CREATE TABLE `' . $db->escape( $table ) . '`' );
			} catch( \Difra\Exception $e ) {
				$currentStr = '';
			};
			if( empty( $currentStr ) ) {
				$res .= "<strong>Table `$table` does not exist:</strong><br/>";
				$res .= "<div class=\"wideInfo\">" . $query->toString() . "</div>";
				continue;
			}
			$currentFragments = $this->chop( $currentStr['Create Table'] );
			$currentObj = $this->getQueries( $currentFragments );
			if( ( $q1 = $query->toString() ) != ( $q2 = $currentObj[0]->toString() ) ) {
				$res .= "<strong>Table `$table` is different:</strong><br/>";
				$res .= "<div class=\"wideInfo\">Current:<br/>" . $q2 . "<br/>";
				$res .= "Described:<br/>" . $q1 . "</div>";
			}
		}
		$res2 = '';
		$current = $db->fetch( 'SHOW TABLES' );
		if( !empty( $current ) ) {
			foreach( $current as $t ) {
				$t1 = array_pop( $t );
				if( !in_array( $t1, $tables ) ) {
					$td = $db->fetchRow( 'SHOW CREATE TABLE `' . $db->escape( $t1 ) . "`" );
					$res2 .= "<strong>Table `$t1` is not described:</strong><br/>";
					$res2 .= "<div class=\"wideInfo\">{$td['Create Table']}</div>";
				}
			}
		}
		return $res2 . $res;
	}
}