<?php

namespace Difra\Param\Filters;

/**
 * Based on PHP Input Filter by Daniel Morris
 * /cast [target=Daniel Morris] Лучи поноса (за $source = html_entity_decode($source, ENT_QUOTES, "ISO-8859-1"))
 */

class HTML {

	// allowed tags
	protected $tagsArray = array( 'a', 'div', 'em', 'li', 'ol', 'p', 'pre', 'span', 'strike', 'u', 'ul', 'img' );
	// allowed attributes
	protected $attrArray = array( 'href', 'src' );

	protected $tagsMethod; // default = 0
	protected $attrMethod; // default = 0

	protected $xssAuto; // default = 1
	protected $tagBlacklist = array( 'applet', 'body', 'bgsound', 'base', 'basefont', 'embed', 'frame', 'frameset', 'head', 'html', 'id', 'iframe', 'ilayer', 'layer', 'link', 'meta', 'name', 'object', 'script', 'style', 'title', 'xml' );
	protected $attrBlacklist = array( 'action', 'background', 'codebase', 'dynsrc', 'lowsrc' ); // also will strip ALL event handlers

	static public function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Constructor for inputFilter class. Only first parameter is required.
	 * @access constructor
	 * @param array $tagsArray - list of user-defined tags
	 * @param array $attrArray - list of user-defined attributes
	 * @param int $tagsMethod - 0= allow just user-defined, 1= allow all but user-defined
	 * @param int $attrMethod - 0= allow just user-defined, 1= allow all but user-defined
	 * @param int $xssAuto - 0= only auto clean essentials, 1= allow clean blacklisted tags/attr
	 * @return \Difra\Param\Filters\HTML
	 *
	 */
	public function __construct( $tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1 ) {

		if( !empty( $tagsArray ) ) {
			for( $i = 0; $i < count( $tagsArray ); $i++ ) {
				$tagsArray[$i] = strtolower( $tagsArray[$i] );
			}
			$this->tagsArray = (array) $tagsArray;
		}
		if( !empty( $attrArray ) ) {
			for( $i = 0; $i < count( $attrArray ); $i++ ) {
				$attrArray[$i] = strtolower( $attrArray[$i] );
			}
			$this->attrArray = (array) $attrArray;
		}
		$this->tagsMethod = $tagsMethod;
		$this->attrMethod = $attrMethod;
		$this->xssAuto = $xssAuto;
	}

	/**
	 * Method to be called by another php script. Processes for XSS and specified bad code.
	 * @access public
	 * @param string|array $source - input string/array-of-string to be 'cleaned'
	 * @return string $source - 'cleaned' version of input parameter
	 */
	public function process( $source ) {
		// clean all elements in this array
		if( is_array( $source ) ) {
			foreach( $source as $key => $value ) // filter element for XSS and other 'bad' code etc.
			{
				if( is_string( $value ) ) {
					$source[$key] = $this->remove( $this->decode( $value ) );
				}
			}
			return $source;
			// clean this string
		} else {
			if( is_string( $source ) ) {
				// filter source for XSS and other 'bad' code etc.
				return $this->remove( $this->decode( $source ) );
				// return parameter as given
			} else {
				return $source;
			}
		}
	}

	/**
	 * Internal method to iteratively remove all unwanted tags and attributes
	 * @access protected
	 * @param String $source - input string to be 'cleaned'
	 * @return String $source - 'cleaned' version of input parameter
	 */
	protected function remove( $source ) {
		$loopCounter = 0;
		// provides nested-tag protection
		while( $source != $this->filterTags( $source ) ) {
			$source = $this->filterTags( $source );
			$loopCounter++;
		}
		return $source;
	}

	/**
	 * Internal method to strip a string of certain tags
	 * @access protected
	 * @param String $source - input string to be 'cleaned'
	 * @return String $source - 'cleaned' version of input parameter
	 */
	protected function filterTags( $source ) {
		// filter pass setup
		$preTag = NULL;
		$postTag = $source;
		// find initial tag's position
		$tagOpen_start = mb_strpos( $source, '<' );
		// interate through string until no tags left
		while( $tagOpen_start !== FALSE ) {
			// process tag interatively
			$preTag .= mb_substr( $postTag, 0, $tagOpen_start );
			$postTag = mb_substr( $postTag, $tagOpen_start );
			$fromTagOpen = substr( $postTag, 1 );
			// end of tag
			$tagOpen_end = mb_strpos( $fromTagOpen, '>' );
			if( $tagOpen_end === false ) {
				break;
			}
			// next start of tag (for nested tag assessment)
			$tagOpen_nested = mb_strpos( $fromTagOpen, '<' );
			if( ( $tagOpen_nested !== false ) && ( $tagOpen_nested < $tagOpen_end ) ) {
				$preTag .= mb_substr( $postTag, 0, ( $tagOpen_nested + 1 ) );
				$postTag = mb_substr( $postTag, ( $tagOpen_nested + 1 ) );
				$tagOpen_start = strpos( $postTag, '<' );
				continue;
			}
			$tagOpen_nested = ( mb_strpos( $fromTagOpen, '<' ) + $tagOpen_start + 1 );
			$currentTag = mb_substr( $fromTagOpen, 0, $tagOpen_end );
			$tagLength = mb_strlen( $currentTag );
			if( !$tagOpen_end ) {
				$preTag .= $postTag;
				$tagOpen_start = mb_strpos( $postTag, '<' );
			}
			// iterate through tag finding attribute pairs - setup
			$tagLeft = $currentTag;
			$attrSet = array( );
			$currentSpace = mb_strpos( $tagLeft, ' ' );
			// is end tag
			if( substr( $currentTag, 0, 1 ) == "/" ) {
				$isCloseTag = TRUE;
				list( $tagName ) = explode( ' ', $currentTag );
				$tagName = mb_substr( $tagName, 1 );
				// is start tag
			} else {
				$isCloseTag = FALSE;
				list( $tagName ) = explode( ' ', $currentTag );
			}
			// excludes all "non-regular" tagnames OR no tagname OR remove if xssauto is on and tag is blacklisted
			if( ( !preg_match( "/^[a-z][a-z0-9]*$/i", $tagName ) ) || ( !$tagName ) || ( ( in_array( mb_strtolower( $tagName ), $this->tagBlacklist ) ) && ( $this->xssAuto ) ) ) {
				$postTag = mb_substr( $postTag, ( $tagLength + 2 ) );
				$tagOpen_start = mb_strpos( $postTag, '<' );
				// don't append this tag
				continue;
			}
			// this while is needed to support attribute values with spaces in!
			while( $currentSpace !== FALSE ) {
				$fromSpace = mb_substr( $tagLeft, ( $currentSpace + 1 ) );
				$nextSpace = mb_strpos( $fromSpace, ' ' );
				$openQuotes = mb_strpos( $fromSpace, '"' );
				$closeQuotes = mb_strpos( substr( $fromSpace, ( $openQuotes + 1 ) ), '"' ) + $openQuotes + 1;
				// another equals exists
				if( strpos( $fromSpace, '=' ) !== FALSE ) {
					// opening and closing quotes exists
					if( ( $openQuotes !== FALSE ) && ( mb_strpos( mb_substr( $fromSpace, ( $openQuotes + 1 ) ), '"' ) !== FALSE ) ) {
						$attr = mb_substr( $fromSpace, 0, ( $closeQuotes + 1 ) );
					} // one or neither exist
					else {
						$attr = mb_substr( $fromSpace, 0, $nextSpace );
					}
					// no more equals exist
				} else {
					$attr = mb_substr( $fromSpace, 0, $nextSpace );
				}
				// last attr pair
				if( !$attr ) {
					$attr = $fromSpace;
				}
				// add to attribute pairs array
				$attrSet[] = $attr;
				// next inc
				$tagLeft = mb_substr( $fromSpace, mb_strlen( $attr ) );
				$currentSpace = mb_strpos( $tagLeft, ' ' );
			}
			// appears in array specified by user
			$tagFound = in_array( mb_strtolower( $tagName ), $this->tagsArray );
			// remove this tag on condition
			if( ( !$tagFound && $this->tagsMethod ) || ( $tagFound && !$this->tagsMethod ) ) {
				// reconstruct tag with allowed attributes
				if( !$isCloseTag ) {
					$attrSet = $this->filterAttr( $attrSet );
					$preTag .= '<' . $tagName;
					for( $i = 0; $i < count( $attrSet ); $i++ ) {
						$preTag .= ' ' . $attrSet[$i];
					}
					// reformat single tags to XHTML
					if( strpos( $fromTagOpen, "</" . $tagName ) ) {
						$preTag .= '>';
					} else {
						$preTag .= ' />';
					}
					// just the tagname
				} else {
					$preTag .= '</' . $tagName . '>';
				}
			}
			// find next tag's start
			$postTag = mb_substr( $postTag, ( $tagLength + 2 ) );
			$tagOpen_start = mb_strpos( $postTag, '<' );
		}
		// append any code after end of tags
		$preTag .= $postTag;
		return $preTag;
	}

	/**
	 * Internal method to strip a tag of certain attributes
	 * @access protected
	 * @param Array $attrSet
	 * @return Array $newSet
	 */
	protected function filterAttr( $attrSet ) {
		$newSet = array( );
		// process attributes
		for( $i = 0; $i < count( $attrSet ); $i++ ) {
			// skip blank spaces in tag
			if( !$attrSet[$i] ) {
				continue;
			}
			// split into attr name and value
			$attrSubSet = explode( '=', trim( $attrSet[$i] ) );
			list( $attrSubSet[0] ) = explode( ' ', $attrSubSet[0] );
			// removes all "non-regular" attr names AND also attr blacklisted
			if( ( !ctype_alnum( $attrSubSet[0] ) ) || ( ( $this->xssAuto ) && ( ( in_array( mb_strtolower( $attrSubSet[0] ), $this->attrBlacklist ) ) || ( mb_substr( $attrSubSet[0], 0, 2 ) == 'on' ) ) ) ) {
				continue;
			}
			// xss attr value filtering
			if( $attrSubSet[1] ) {
				// strips unicode, hex, etc
				$attrSubSet[1] = str_replace( '&#', '', $attrSubSet[1] );
				// strip normal newline within attr value
				$attrSubSet[1] = preg_replace( '/\s+/', '', $attrSubSet[1] );
				// strip double quotes
				$attrSubSet[1] = str_replace( '"', '', $attrSubSet[1] );
				// [requested feature] convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr value)
				if( ( mb_substr( $attrSubSet[1], 0, 1 ) == "'" ) && ( mb_substr( $attrSubSet[1], ( mb_strlen( $attrSubSet[1] ) - 1 ), 1 ) == "'" ) ) {
					$attrSubSet[1] = mb_substr( $attrSubSet[1], 1, ( mb_strlen( $attrSubSet[1] ) - 2 ) );
				}
				// strip slashes
				$attrSubSet[1] = stripslashes( $attrSubSet[1] );
			}
			// auto strip attr's with "javascript:
			if( ( ( mb_strpos( strtolower( $attrSubSet[1] ), 'expression' ) !== false ) && ( mb_strtolower( $attrSubSet[0] ) == 'style' ) ) || ( mb_strpos( mb_strtolower( $attrSubSet[1] ), 'javascript:' ) !== false ) || ( mb_strpos( mb_strtolower( $attrSubSet[1] ), 'behaviour:' ) !== false ) || ( mb_strpos( mb_strtolower( $attrSubSet[1] ), 'vbscript:' ) !== false ) || ( mb_strpos( mb_strtolower( $attrSubSet[1] ), 'mocha:' ) !== false ) || ( mb_strpos( mb_strtolower( $attrSubSet[1] ), 'livescript:' ) !== false )
			) {
				continue;
			}

			// if matches user defined array
			$attrFound = in_array( mb_strtolower( $attrSubSet[0] ), $this->attrArray );
			// keep this attr on condition
			if( ( !$attrFound && $this->attrMethod ) || ( $attrFound && !$this->attrMethod ) ) {
				// attr has value
				if( $attrSubSet[1] ) {
					$newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
				} // attr has decimal zero as value
				else {
					if( $attrSubSet[1] == "0" ) {
						$newSet[] = $attrSubSet[0] . '="0"';
					} // reformat single attributes to XHTML
					else {
						$newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
					}
				}
			}
		}
		return $newSet;
	}

	/**
	 * Try to convert to plaintext
	 * @access protected
	 * @param String $source
	 * @return String $source
	 */
	protected function decode( $source ) {
		// url decode
		$source = html_entity_decode( $source, ENT_QUOTES, "UTF-8" );
		// convert decimal
		$source = preg_replace( '/&#(\d+);/me', "chr(\\1)", $source ); // decimal notation
		// convert hex
		$source = preg_replace( '/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $source ); // hex notation
		return $source;
	}
}

?>
