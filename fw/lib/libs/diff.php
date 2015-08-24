<?php

namespace Difra\Libs;

/**
 * Class Diff
 *
 * @deprecated
 * @package Difra\Libs
 */
class Diff
{
	/**
	 * Get diff for two arrays of strings as if it is a text files
	 *
	 * @param string[] $array1
	 * @param string[] $array2
	 * @return bool
	 */
	static public function diffArrays($array1, $array2)
	{
		$res = self::_diffArrays($array1, $array2);
		return $res ? $res['data'] : false;
	}

	/**
	 * diffArrays() recursive implementation
	 *
	 * @param string[] $array1
	 * @param string[] $array2
	 * @param array    $result
	 * @param int      $i1
	 * @param int      $i2
	 * @param int      $depth
	 * @return array
	 */
	static private function _diffArrays($array1, $array2, $result = [], $i1 = 0, $i2 = 0, $depth = 0)
	{
		// equal lines
		while (isset($array1[$i1]) and isset($array2[$i2]) and $array1[$i1] == $array2[$i2]) {
			$result[] = ['sign' => '=', 'value' => $array1[$i1++]];
			$i2++;
			$depth++;
		}
		// end of $array1
		while (!isset($array1[$i1]) and isset($array2[$i2])) {
			$result[] = ['sign' => '+', 'value' => $array2[$i2++]];
			$depth++;
		}
		// end of $array2
		while (!isset($array2[$i2]) and isset($array1[$i1])) {
			$result[] = ['sign' => '-', 'value' => $array1[$i1++]];
			$depth++;
		}
		// end of both arrays
		if (!isset($array1[$i1]) and !isset($array2[$i2])) {
			return ['depth' => $depth, 'data' => $result];
		}

		// lines do not match

		// get line from $array1
		if (isset($array1[$i1])) {
			$result1 = $result;
			$i1a = $i1;
			$da = $depth;
			$a2a = array_slice($array2, $i2);
			do {
				$result1[] = ['sign' => '-', 'value' => $array1[$i1a++]];
				$da++;
			} while (isset($array1[$i1a]) and !in_array($array1[$i1a], $a2a));
			$res1 = self::_diffArrays($array1, $array2, $result1, $i1a, $i2, $da);
		} else {
			$res1 = false;
		}
		// get line from $array2
		if (isset($array2[$i2])) {
			$result1 = $result;
			$i2a = $i2;
			$da = $depth;
			$a1a = array_slice($array1, $i1);
			do {
				$result1[] = ['sign' => '+', 'value' => $array2[$i2a++]];
				$da++;
			} while (isset($array2[$i2a]) and !in_array($array2[$i2a], $a1a));
			$res2 = self::_diffArrays($array1, $array2, $result1, $i1, $i2a, $da);
		} else {
			$res2 = false;
		}
		// get result with less steps
		if (!$res1) {
			return $res2;
		} elseif (!$res2) {
			return false;
		} elseif ($res1['depth'] <= $res2['depth']) {
			return $res1;
		} else {
			return $res2;
		}
	}
}
