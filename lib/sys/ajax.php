<?php
/**
 * Slightly based on Subsys_JsHttpRequest_Php by Dmitry Koterov
 */

class Ajax {

	public $isAjax = false;
	public $params = array();
	public $response = array();
    
	public function __construct() {
		$this->isAjax = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		if( $this->isAjax ) {
			$this->params = $this->getRequest();
		}
	}
    
	static function getInstance() {
		static $_instance = null;
		if( !$_instance ) {
			$_instance = new self;
		}
		return $_instance;
	}

	// Parse & decode QUERY_STRING
	private function getRequest() {
		$request = array();
		foreach( array( '_GET'  => $_SERVER['QUERY_STRING'], '_POST' => @$GLOBALS['HTTP_RAW_POST_DATA'] ) as $dst => $src ) {
			if( isset( $GLOBALS[$dst] ) ) {
				$s = preg_replace( '/%(?!5B)(?!5D)([0-9a-f]{2})/si', '%u00\\1', $src );
				parse_str( $s, $data );
				$request = array_merge( $request, $this->_ucs2EntitiesDecode( $data ) );
			}
		}
		return $request;
	}

	public function setResponse( $param, $value ) {
		$this->response[$param] = $value;
	}

	public function getResponse() {

		if( !empty( $this->response ) ) {
			$id = preg_match( '/(\d+)((?:-\w+)?)$/s', $_SERVER['QUERY_STRING'], $m ) ? $m[1] : 0;
			return "Subsys_JsHttpRequest_Js.dataReady(\n" .
				"  " . $this->_php2js( $id ) . "," .
				"  " . $this->_php2js( '' ) . ",\n" .
				"  " . $this->_php2js( $this->response ) . "\n)";
		}
	}

	private function _php2js( $a ) {

		// if boolean or null
		if( is_null( $a ) ) {
			return 'null';
		}
    		if( is_bool( $a ) ) {
    			return $a ? 'true' : 'false';
		}
		
		// if scalar
		if( is_scalar( $a ) ) {
			$a = addslashes($a);
			$a = str_replace("\n", '\n', $a);
			$a = str_replace("\r", '\r', $a);
			return "'$a'";
		}
		
		// is it list or array?
		$isList = true;
		for( $i = 0, reset( $a ); $i < count( $a ); $i++, next( $a ) ) {
			if( key( $a ) !== $i ) {
				$isList = false;
				break;
			}
		}
		$result = array();
		if( $isList ) {
			// if list
			foreach( $a as $v ) {
				$result[] = self::_php2js( $v );
			}
			return '[ ' . join( ',', $result ) . ' ]';
		} else {
			// if array
			foreach( $a as $k => $v ) {
				$result[] = self::_php2js( $k ) . ': ' . self::_php2js( $v );
			}
			return '{ ' . join(',', $result) . ' }';
		}
	}
    
	// Called in case of error too!
	function _obHandler( $text ) {
		// Check for error.
		if (preg_match('{'.$this->UNIQ_HASH.'(.*?)'.$this->UNIQ_HASH.'}sx', $text)) {
			$text = str_replace($this->UNIQ_HASH, '', $text);
			$this->WAS_ERROR = 1;
		}
		// Content-type header.
		// In XMLHttpRRequest mode we must return text/plain - damned stupid Opera 8.0. :(
		header( 'Content-type: text/plain' );
		// Make resulting hash.
		if (!isset($this->RESULT)) $this->RESULT = @$GLOBALS['_RESULT'];
		$result = $this->_php2js($this->RESULT);
		$text = 
			"Subsys_JsHttpRequest_Js.dataReady(\n" .
			"  " . $this->_php2js($this->SCRIPT_ID) . "," . 
			"  " . $this->_php2js(trim($text)) . ",\n" .
			"  " . $result . "\n" .
			")";
		// $f = fopen("debug", "w"); fwrite($f, $text); fclose($f);
		return $text;
	}
    
	// Decode all %uXXXX entities in string or array (recurrent).
	// String must not contain %XX entities - they are ignored!
	function _ucs2EntitiesDecode($data) {
		if (is_array($data)) {
			$d = array();
			foreach ($data as $k=>$v) {
				$d[$this->_ucs2EntitiesDecode($k)] = $this->_ucs2EntitiesDecode($v);
			}
			return $d;
		} else {
			if (strpos($data, '%u') !== false) { // improve speed
				$data = preg_replace_callback('/%u([0-9A-F]{1,4})/si', array(&$this, '_ucs2EntitiesDecodeCallback'), $data);
			}
	    		return $data;
		}
    	}
    
	// Decode one %uXXXX entity (RE callback).
	function _ucs2EntitiesDecodeCallback( $p ) {
		$hex = $p[1];
		$dec = hexdec($hex);
		$c = @iconv('UCS-2BE', 'UTF-8', pack('n', $dec));
		return $c ? $c : "&#$dec;";
	}
}

