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

	/*
	private function collectCurrent() {

		$db = \Difra\MySQL::getInstance();
		$list = $db->fetch( 'SHOW TABLES' );
		$tables = array();
		foreach( $list as $table ) {
			$t = $db->fetchRow( 'SHOW CREATE TABLE `' . array_pop( $table ) . '`' );
			$tables[] = array_pop( $t );
		}
		return $this->parseDump( $tables );
	}

	private function compare( $dump ) {

		return false;
	}
	*/

	private function parseDump( $dump ) {

		$fragments = $this->chop( $dump );
//		var_dump( $fragments );
	}

	private function chop( $text, $recursive = false ) {

		$shards = array();
		$next = '';
		$i = 0;
		$size = strlen( $text );
		$comm = $linecomm = false;
		$str = '';
		while( $i < $size ) {
			$a = $text{$i};
			$a1 = ( $i < $size - 1 ) ? $text{$i+1} : '';
			$i++;

			/* Строки */
			// проверка на конец строки
			if( $str !== '' ) {
				$str .= $a;
				if( $str{0} == $a and substr( $str, -1 ) != '\\' ) {
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
			if( $a == '-' and $a1 == '-' and $this->isSpace( $text{$i+1} ) ) {
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
				$res = $this->chop( substr( $text, $i ), true );
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

			if( $a == ',' or $a == ';' ) {
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
}