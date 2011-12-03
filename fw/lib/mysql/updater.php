<?php

namespace Difra\MySQL;

class Updater {

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	public function check() {

		$current = $this->collectGoal();
//		$diffs = $this->compare( $current );
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
		$queries = $this->getQueries( $fragments );
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
}