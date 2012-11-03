<?php

namespace Difra\MySQL;

class Parser {

	/**
	 * Сравнивает структуру SQL-таблиц с состоянием, сохраненным в файлах bin/db.sql и возвращает результат в XML.
	 *
	 * @param $node
	 *
	 * @return array|bool
	 */
	public static function getStatusXML( $node ) {

		$classList     = array();
		$currentChunks = self::chop( self::getCurrentSQL() );
		if( !empty( $currentChunks ) ) {
			foreach( $currentChunks as $chunks ) {
				if( $class = self::getChunksClass( $chunks ) ) {
					$classList[$class] = 1;
					$class::create( $chunks );
				} // ничего, кроме таблиц в данный момент в current попадать не должно
			}
		}
		$goalChunks = self::chop( self::getGoalSQL() );
		if( !empty( $goalChunks ) ) {
			foreach( $goalChunks as $chunks ) {
				if( $class = self::getChunksClass( $chunks ) ) {
					$classList[$class] = 1;
					if( method_exists( $class, 'autoGoal' ) ) {
						$class::autoGoal( $chunks );
					} // цели других типов проверяем туточки
				}
			}
		}
		if( !empty( $classList ) ) {
			foreach( $classList as $class => $v ) {
				$list = $class::getList();
				foreach( $list as $item ) {
					$item->getStatusXML( $node );
				}
			}
		}
	}

	/**
	 * Получение данных из файлов bin/db.sql в первоначальном текстовом виде.
	 *
	 * @return string
	 */
	public static function getGoalSQL() {

		$paths   = \Difra\Plugger::getInstance()->getPaths();
		$paths[] = DIR_FW;
		$paths[] = DIR_ROOT;
		$tables  = array();
		foreach( $paths as $path ) {
			if( is_readable( $path . '/bin/db.sql' ) ) {
				$tables[] = file_get_contents( $path . '/bin/db.sql' );
			}
		}
		return implode( "\n", $tables );
	}

	/**
	 * Получение текущей структуры таблиц в текстовом виде.
	 * @param bool $asArray
	 *
	 * @return string|array
	 */
	public static function getCurrentSQL( $asArray = false ) {

		$db     = \Difra\MySQL::getInstance();
		$tables = $db->fetch( 'SHOW TABLES' );
		if( empty( $tables ) ) {
			return false;
		}
		$tablesSQL = array();
		foreach( $tables as $table ) {
			$tableName   = array_pop( $table );
			$t           = $db->fetchRow( 'SHOW CREATE TABLE `' . $db->escape( $tableName ) . '`' );
			$tablesSQL[] = array_pop( $t );
		}
		if( $asArray ) {
			return $tablesSQL;
		} else {
			return implode( ";\n", $tablesSQL );
		}
	}

	/**
	 * Разбивает SQL-строку на части с учётом строк, комментариев и т.д.
	 *
	 * @param string $text                SQL-строка
	 * @param bool   $tree                Части, заключённые в скобки, добавляются как ветки дерева
	 * @param bool   $recursive           Внутренний параметр
	 *
	 * @return array
	 */
	private static function chop( $text, $tree = false, $recursive = false ) {

		$lines  = array();
		$shards = array();
		$next   = '';
		$i      = 0;
		$size   = mb_strlen( $text );
		$comm   = $linecomm = false;
		$str    = '';
		while( $i < $size ) {
			$a  = mb_substr( $text, $i, 1 );
			$a1 = ( $i < $size - 1 ) ? mb_substr( $text, $i + 1, 1 ) : '';
			$i++;

			/* Строки */
			// проверка на конец строки
			if( $str !== '' ) {
				$str .= $a;
				if( mb_substr( $str, 0, 1 ) == $a and mb_substr( $str, -1 ) != '\\' ) {
					$shards[] = $str;
					$str      = '';
				}
				continue;
			}
			// проверка на начало строки
			if( !$comm and !$linecomm ) {
				if( $a == '"' or $a == "'" or $a == '`' ) {
					if( $next !== '' ) {
						$shards[] = $next;
						$next     = '';
					}
					$str = $a;
					continue;
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
					$next     = '';
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
			if( $a == '-' and $a1 == '-' and \Difra\Libs\Strings::isWhitespace( mb_substr( $text, $i + 1, 1 ) ) ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next     = '';
				}
				$linecomm = true;
				$i += 2;
				continue;
			}
			// проверка на начало строкового комментария ('# comment')
			if( $a == '#' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next     = '';
				}
				$linecomm = true;
				continue;
			}

			if( $a == '(' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next     = '';
				}
				$res = self::chop( mb_substr( $text, $i ), $tree, true );
				if( $tree ) {
					$shards[] = $res['data'];
				} else {
					$shards = array_merge( $shards, array( '(' ), $res['data'], array( ')' ) );
				}
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

			if( !$recursive and $a == ';' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next     = '';
				}
				if( !empty( $shards ) ) {
					$lines[] = $shards;
				}
				$shards = array();
			} elseif( $a == ',' or $a == ';' or $a == '=' ) {
				if( $next !== '' ) {
					$shards[] = $next;
					$next     = '';
				}
				$shards[] = $a;
				continue;
			} elseif( !\Difra\Libs\Strings::isWhitespace( $a ) ) {
				$next .= $a;
				continue;
			} else {
				if( $next !== '' ) {
					$shards[] = $next;
					$next     = '';
					continue;
				}
			}
		}
		if( $next !== '' ) {
			$shards[] = $next;
		}
		if( $recursive ) {
			return $shards;
		}
		if( !empty( $lines ) ) {
			$lines[] = $shards;
		}
		return $lines;
	}

	/**
	 * Возвращает имя класса для загрузки SQL-команды, порубленной на чанки
	 *
	 * @param $chunks
	 *
	 * @return string|null
	 */
	public static function getChunksClass( $chunks ) {

		if( sizeof( $chunks ) >= 2 and $chunks[0] == 'CREATE' and $chunks[1] == 'TABLE' ) {
			return '\Difra\MySQL\SQL\Table';
		}
		return null;
	}

	private static $sep = array( ',' => ', ', ')' => ') ', '(' => ' (' );

	/**
	 * Преобразует набор чанков в строку
	 * @param $array
	 *
	 * @return string
	 */
	public static function def2string( $array ) {

		static $keywords;
		if( !$keywords ) {
			$keywords = include( dirname( __FILE__ ) . '/keywords.php' );
		}
		$res = '';
		$d   = '';
		foreach( $array as $v ) {
			$u = mb_strtoupper( $v );
			if( isset( $keywords[$u] ) ) {
				switch( $keywords[$u] ) {
				case '1':
					$a2 = $u;
					break;
				default:
					$a2 = mb_strtolower( $v );
				}
			} else {
				$a2 = $v;
			}
			if( isset( self::$sep[$a2] ) ) {
				$res .= self::$sep[$a2];
				$d = '';
			} else {
				$res .= $d . $a2;
				$d = ' ';
			}
		}
		return $res;
	}
}