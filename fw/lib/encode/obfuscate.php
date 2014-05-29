<?php

class Obfuscate {

	/*
	 *
	 * STAGE 1
	 *
	 */

	private static function gen( $depthLen, $dimensions, $random ) {

		if( $dimensions == 0 ) {
			return $random ? chr( rand( 32, 126 ) ) : 0;
		}
		$a = array();
		for( $i = 0; $i < $depthLen; $i++ ) {
			$a[$i] = self::gen( $depthLen, $dimensions - 1, $random );
		}
		return $a;
	}

	private static function put( &$array, $coordinates, $position, $value ) {

		if( !is_array( $array[$coordinates[$position]] ) ) {
			$array[$coordinates[$position]] = $value;
		} else {
			self::put( $array[$coordinates[$position]], $coordinates, $position + 1, $value );
		}
	}

	public static function stage1( $e ) {

		// prepare mesh
		$depth = 'iIl';
		$chars = array_unique( str_split( $e ) );
		$dimensions = 1;
		while( ( pow( strlen( $depth ), $dimensions ) ) < sizeof( $chars ) ) {
			$dimensions++;
		}
//		echo "$dimensions dimensions\n\n";
		// generate mesh
		$mesh = self::gen( strlen( $depth ), $dimensions, true );
		$mask = self::gen( strlen( $depth ), $dimensions, false );
		$matches = array();
		// fill mesh with real data
		foreach( $chars as $char ) {
			// get free random coordinates
			do {
				$coordinates = array();
				for( $n = 0; $n < $dimensions; $n++ ) {
					$coordinates[] = rand( 0, strlen( $depth ) - 1 );
				}
				// check random coordinates
				$sub = $mask;
				foreach( $coordinates as $v ) {
					$sub = $sub[$v];
				};
			} while( $sub );
			// remember character and coordinates match
			self::put( $mesh, $coordinates, 0, $char );
			self::put( $mask, $coordinates, 0, 1 );
			$matches[ord( $char )] = $coordinates;
		}
		$code = '?>' . file_get_contents( __DIR__ . '/run.php' );
/*		$code .= '?>' . file_get_contents( __DIR__ . '/function-f.inc' ); */
		$code .= "s1::get()->d=\"" . addcslashes( self::toString( $mesh ), '"\\$' ) . "\";";

		$obf = '';
		for( $i = 0; $i < strlen( $e ); $i++ ) {
			$coordinates = $matches[ord( $e{$i} )];
			for( $j = 0; $j < sizeof( $coordinates ); $j++ ) {
				$obf .= $depth{$coordinates[$j]};
			}
		}
		$code .= "s1::get()->i='" . addcslashes( rtrim( base64_encode( gzcompress( $obf ) ), '=' ), '\'\\' ) . "';s1::get()->r($dimensions,'$depth');";
		return $code;
	}

	private static function toString( $arr ) {

		if( is_string( $arr ) ) {
			return $arr;
		}
		$str = '';
		for( $i = 0; $i < sizeof( $arr ); $i++ ) {
			$str .= self::toString( $arr[$i] );
		}
		return $str;
	}

	/*
	 *
	 * STAGE 2
	 *
	 */

	public static function stage23( $code ) {

		/* $coords2offset = array(
			0 => sha1.start(4), 1=> sha1.length(4),
			2 => s1.start(4), 3=> s1.length(4),
			4 => s2.start(4), 5=> s2.length(4),
			6 => s3.start(4), 7 => s3.length(4) )
		*/

		// get s1 block
		$s1 = base64_encode( $code );

		// get sha block
		$sha1 = base64_encode( sha1( $s1 ) );

		$coords = array();
		$coordsLength = rand( 203, 255 );
		$maxcoord = strlen( $s1 ) * 2;
		for( $i = 0; $i < $coordsLength; $i++ ) {
			$coords[$i] = rand( 0, $maxcoord );
		}
		$coords2 = '';
		foreach( $coords as $k => $v ) {
			$coords2 .= chr( $v >> 8 ) . chr( $v & 0xff );
		}
		$coordsSize = strlen( base64_encode( $coords2 ) );

		$coords2offset = array();
		for( $i = 0; $i < 8; $i++ ) {
			do {
				$r = rand( 0, $coordsLength - 1 );
			} while( in_array( $r, $coords2offset ) );
			$coords2offset[$i] = $r;
		}

		// get s3 block
		$coordsLoad = '$cr=base64_decode(substr($file,-' . $coordsSize . '));' .
			'$c=array();for($ci=0;$ci<strlen($cr);$ci+=2){$c[]=((ord($cr{$ci})<<8)+ord($cr{$ci+1}));};';
/*		$coordsLoad .= "echo \"sha1 =\$c[{$coords2offset[0]}]=\$c[{$coords2offset[1]}]=\n\";";
		$coordsLoad .= "echo \"s1   =\$c[{$coords2offset[2]}]=\$c[{$coords2offset[3]}]=\n\";";
		$coordsLoad .= "echo \"s2   =\$c[{$coords2offset[4]}]=\$c[{$coords2offset[5]}]=\n\";";
		$coordsLoad .= "echo \"s3   =\$c[{$coords2offset[6]}]=\$c[{$coords2offset[7]}]=\n\";"; */
		$s3code = '$file=file_get_contents($_);' .
			'eval(base64_decode("' . base64_encode( $coordsLoad ) . '"));eval(base64_decode(substr($file,$c[' . $coords2offset[4] .
			'],$c[' . $coords2offset[5] . '])));';
		$s3 = '<?php $_=__FILE__;eval(base64_decode("' .
			base64_encode( $s3code ) . '"));__halt_compiler();';

		// get s2 block
		$s2code = 'eval( base64_decode((base64_encode(sha1(substr
			($file,$c[' . $coords2offset[2] . '],$c[' . $coords2offset[3] .
			'])))==substr($file,$c[' . $coords2offset[0] . '],' . '$c[' . $coords2offset[1] . ']))?substr($file,$c[' . $coords2offset[2] .
			'],$c[' . $coords2offset[3] . '] ):"ZWNobyAiU2VnbWVudGF0aW9uIGZhdWx0XG4iOw"));';
		$s2 = base64_encode( "eval(base64_decode(\"" . base64_encode( $s2code ) . "\"));" );

		// sha1.length
		$coords[$coords2offset[1]] = strlen( $sha1 );
		// s1.length
		$coords[$coords2offset[3]] = strlen( $s1 );
		// s2.length
		$coords[$coords2offset[5]] = strlen( $s2 );
		// s3.length
		$coords[$coords2offset[7]] = strlen( $s3 );

		// data order: s3 s1 sha1 s2 coords

		// s3.start
		$coords[$coords2offset[6]] = 0;
		// s1.start = s3.start + s3.length
		$coords[$coords2offset[2]] = $coords[$coords2offset[6]] + $coords[$coords2offset[7]];
		// sha1.start = s1.start + s1.length
		$coords[$coords2offset[0]] = $coords[$coords2offset[2]] + $coords[$coords2offset[3]];
		// s2.start = sha1.start + sha1.length;
		$coords[$coords2offset[4]] = $coords[$coords2offset[0]] + $coords[$coords2offset[1]];

		$coords2 = '';
		foreach( $coords as $k => $v ) {
			$coords2 .= chr( $v >> 8 ) . chr( $v & 0xff );
		}

		return $s3 . $s1 . $sha1 . $s2 . base64_encode( $coords2 );
	}
}

$code = <<<CODE
\$l=file_get_contents( __DIR__ . '/libs/capcha/Simple.ttf' );
eval(substr(\$l,-20)!=hex2bin(sha1(\$i=substr(\$l,0,-20)))?base64_decode('ZWNobyAiU2VnbWVudGF0aW9uIGZhdWx0XG4iOw'):
convert_uudecode(str_replace("\\0","\\nM",strrev(gzinflate(strrev(\$i))))));
CODE;

$code = Obfuscate::stage1( $code );
$code = Obfuscate::stage23( $code );

return $code;