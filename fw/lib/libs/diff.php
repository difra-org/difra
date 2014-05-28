<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

namespace Difra\Libs;

/**
 * Class Diff
 *
 * @package Difra\Libs
 */
class Diff {

	/**
	 * Возвращает массив, описывающий разницу между двумя массивами строк.
	 * Учитывается порядок строк, как если бы это был текстовый файл.
	 * Каждая строка массива-результата — это массив:
	 * array(
	 *         'sign' => '=' (строки есть в обоих массивах), '-' (строки нет во втором массиве), '+' (строки нет в первом массиве)
	 *         'value' => текст строки
	 * )
	 *
	 * @param string[] $array1
	 * @param string[] $array2
	 *
	 * @return bool
	 */
	static public function diffArrays( $array1, $array2 ) {

		$res = self::_diffArrays( $array1, $array2 );
		return $res ? $res['data'] : false;
	}

	/**
	 * Рекурсивная часть функции diffArrays()
	 *
	 * @param string[] $array1 Массив 1
	 * @param string[] $array2 Массив 2
	 * @param array    $result Внутренний параметр
	 * @param int      $i1     Внутренний параметр
	 * @param int      $i2     Внутренний параметр
	 * @param int      $depth  Внутренний параметр
	 *
	 * @return array
	 */
	static private function _diffArrays( $array1, $array2, $result = array(), $i1 = 0, $i2 = 0, $depth = 0 ) {

		// копируем совпадающие строки
		while( isset( $array1[$i1] ) and isset( $array2[$i2] ) and $array1[$i1] == $array2[$i2] ) {
			$result[] = array( 'sign' => '=', 'value' => $array1[$i1++] );
			$i2++;
			$depth++;
		}
		// массив 1 закончился?
		while( !isset( $array1[$i1] ) and isset( $array2[$i2] ) ) {
			$result[] = array( 'sign' => '+', 'value' => $array2[$i2++] );
			$depth++;
		}
		// массив 2 закончился?
		while( !isset( $array2[$i2] ) and isset( $array1[$i1] ) ) {
			$result[] = array( 'sign' => '-', 'value' => $array1[$i1++] );
			$depth++;
		}
		if( !isset( $array1[$i1] ) and !isset( $array2[$i2] ) ) {
			// оба массива закончились — возвращаем результат
			return array( 'depth' => $depth, 'data' => $result );
		}

		// строки не совпадают

		// берём строки из первого
		if( isset( $array1[$i1] ) ) {
			$result1 = $result;
			$i1a = $i1;
			$da = $depth;
			$a2a = array_slice( $array2, $i2 );
			do {
				$result1[] = array( 'sign' => '-', 'value' => $array1[$i1a++] );
				$da++;
			} while( isset( $array1[$i1a] ) and !in_array( $array1[$i1a], $a2a ) );
			$res1 = self::_diffArrays( $array1, $array2, $result1, $i1a, $i2, $da );
		} else {
			$res1 = false;
		}
		// берём строку из второго
		if( isset( $array2[$i2] ) ) {
			$result1 = $result;
			$i2a = $i2;
			$da = $depth;
			$a1a = array_slice( $array1, $i1 );
			do {
				$result1[] = array( 'sign' => '+', 'value' => $array2[$i2a++] );
				$da++;
			} while( isset( $array2[$i2a] ) and !in_array( $array2[$i2a], $a1a ) );
			$res2 = self::_diffArrays( $array1, $array2, $result1, $i1, $i2a, $da );
		} else {
			$res2 = false;
		}
		// возвращаем результат, принесший победу за меньшее число шагов
		if( !$res1 ) {
			return $res2;
		} elseif( !$res2 ) {
			return false;
		} elseif( $res1['depth'] <= $res2['depth'] ) {
			return $res1;
		} else {
			return $res2;
		}
	}
}