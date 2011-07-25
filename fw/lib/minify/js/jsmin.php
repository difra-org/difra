<?php
/**
 * Основано на jsmin.php - PHP implementation of Douglas Crockford's JSMin.c.
 */

namespace Difra\Minify\JS;
use Difra;

class JSMin {

	const ACTION_KEEP_A = 1;
	const ACTION_DELETE_A = 2;
	const ACTION_DELETE_A_B = 3;

	protected $a = '';
	protected $b = '';
	protected $input = '';
	protected $inputIndex = 0;
	protected $inputLength = 0;
	protected $lookAhead = null;
	protected $output = '';

	// -- Public Static Methods --------------------------------------------------

	/**
	 * Minify Javascript
	 *
	 * @uses __construct()
	 * @uses min()
	 * @param string $js Javascript to be minified
	 * @return string
	 */
	public static function minify( $js ) {
		$jsmin = new JSMin( $js );
		return $jsmin->min();
	}

	// -- Public Instance Methods ------------------------------------------------

	/**
	 * Constructor
	 *
	 * @param string $input Javascript to be minified
	 */
	public function __construct( $input ) {
		$this->input = str_replace( "\r", "\n", $input );
		$this->inputLength = strlen( $this->input );
	}

	// -- Protected Instance Methods ---------------------------------------------

	/**
	 * Action -- do something! What to do is determined by the $command argument.
	 *
	 * action treats a string as a single character. Wow!
	 * action recognizes a regular expression if it is preceded by ( or , or =.
	 *
	 * @uses next()
	 * @uses get()
	 * @throws JSMinException If parser errors are found:
	 *	 - Unterminated string literal
	 *	 - Unterminated regular expression set in regex literal
	 *	 - Unterminated regular expression literal
	 * @param int $command One of class constants:
	 *      ACTION_KEEP_A      Output A. Copy B to A. Get the next B.
	 *      ACTION_DELETE_A    Copy B to A. Get the next B. (Delete A).
	 *      ACTION_DELETE_A_B  Get the next B. (Delete B).
	 */
	protected function action( $command ) {
		switch( $command ) {
		case self::ACTION_KEEP_A:
			$this->output .= $this->a;

		case self::ACTION_DELETE_A:
			$this->a = $this->b;

			if( $this->a === "'" || $this->a === '"' ) {
				while( true ) {
					$this->output .= $this->a;
					$this->a = $this->get();

					if( $this->a === $this->b ) {
						break;
					}

					if( $this->a <= "\n" ) {
						throw new JSMinException( 'Unterminated string literal.' );
					}

					if( $this->a === '\\' ) {
						$this->output .= $this->a;
						$this->a = $this->get();
					}
				}
			}

		case self::ACTION_DELETE_A_B:
			$this->b = $this->next();

			if( $this->b === '/'
			    && ( $this->a === '(' || $this->a === ',' || $this->a === '='
				 || $this->a === ':'
				 || $this->a === '['
				 || $this->a === '!'
				 || $this->a === '&'
				 || $this->a === '|'
				 || $this->a === '?'
				 || $this->a === '{'
				 || $this->a === '}'
				 || $this->a === ';'
				 || $this->a === "\n" )
			) {

				$this->output .= $this->a . $this->b;

				while( true ) {
					$this->a = $this->get();

					if( $this->a === '[' ) {
						/*
						inside a regex [...] set, which MAY contain a '/' itself. Example: mootools Form.Validator near line 460:
						return Form.Validator.getValidator('IsEmpty').test(element) || (/^(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]\.?){0,63}[a-z0-9!#$%&'*+/=?^_`{|}~-]@(?:(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\])$/i).test(element.get('value'));
						*/
						while( true ) {
							$this->output .= $this->a;
							$this->a = $this->get();

							if( $this->a === ']' ) {
								break;
							} elseif( $this->a === '\\' ) {
								$this->output .= $this->a;
								$this->a = $this->get();
							} elseif( $this->a <= "\n" ) {
								throw new JSMinException( 'Unterminated regular expression set in regex literal.' );
							}
						}
					} elseif( $this->a === '/' ) {
						break;
					} elseif( $this->a === '\\' ) {
						$this->output .= $this->a;
						$this->a = $this->get();
					} elseif( $this->a <= "\n" ) {
						throw new JSMinException( 'Unterminated regular expression literal.' );
					}

					$this->output .= $this->a;
				}

				$this->b = $this->next();
			}
		}
	}

	/**
	 * Get next char. Convert ctrl char to space.
	 *
	 * @return string|null
	 */
	protected function get() {
		$c = $this->lookAhead;

		if( $c === null and $this->inputIndex < $this->inputLength ) {
			$c = $this->input{$this->inputIndex++};
		} else {
			$this->lookAhead = null;
		}

		if( $c >= ' ' or $c === null or $c === "\n" ) {
			return $c;
		}

		return ' ';
	}

	/**
	 * Is $c a letter, digit, underscore, dollar sign, or non-ASCII character.
	 *
	 * @return bool
	 */
	protected function isAlphaNum( $c ) {
		return ( 'a' <= $c and $c <= 'z' ) or ( $c >= 'A' and $c <= 'Z' ) or ( $c >= '0' and $c <= '9' ) or $c == '_' or $c == '$' or $c > '~' or
						      $c == '\\';
	}

	/**
	 * Perform minification, return result
	 *
	 * @uses action()
	 * @uses isAlphaNum()
	 * @return string
	 */
	protected function min() {
		$this->a = "\n";
		$this->action( self::ACTION_DELETE_A_B );

		while( $this->a !== null ) {
			switch( $this->a ) {
			case ' ':
				if( $this->isAlphaNum( $this->b ) ) {
					$this->action( self::ACTION_KEEP_A );
				} else {
					$this->action( self::ACTION_DELETE_A );
				}
				break;

			case "\n":
				switch( $this->b ) {
				case '{':
				case '[':
				case '(':
				case '+':
				case '-':
					$this->action( self::ACTION_KEEP_A );
					break;

				case ' ':
					$this->action( self::ACTION_DELETE_A_B );
					break;

				default:
					if( $this->isAlphaNum( $this->b ) ) {
						$this->action( self::ACTION_KEEP_A );
					} else {
						$this->action( self::ACTION_DELETE_A );
					}
				}
				break;

			default:
				switch( $this->b ) {
				case ' ':
					if( $this->isAlphaNum( $this->a ) ) {
						$this->action( self::ACTION_KEEP_A );
						break;
					}

					$this->action( self::ACTION_DELETE_A_B );
					break;

				case "\n":
					switch( $this->a ) {
					case '}':
					case ']':
					case ')':
					case '+':
					case '-':
					case '"':
					case "'":
						$this->action( self::ACTION_KEEP_A );
						break;

					default:
						if( $this->isAlphaNum( $this->a ) ) {
							$this->action( self::ACTION_KEEP_A );
						} else {
							$this->action( self::ACTION_DELETE_A_B );
						}
					}
					break;

				default:
					$this->action( self::ACTION_KEEP_A );
					break;
				}
			}
		}

		return $this->output;
	}

	/**
	 * Get the next character, skipping over comments. peek() is used to see
	 *  if a '/' is followed by a '/' or '*'.
	 *
	 * @uses get()
	 * @uses peek()
	 * @throws JSMinException On unterminated comment.
	 * @return string
	 */
	protected function next() {
		$c = $this->get();

		if( $c === '/' ) {
			switch( $this->peek() ) {
			case '/':
				while( true ) {
					$c = $this->get();

					if( $c <= "\n" ) {
						return $c;
					}
				}

			case '*':
				$this->get();

				while( true ) {
					switch( $this->get() ) {
					case '*':
						if( $this->peek() === '/' ) {
							$this->get();
							return ' ';
						}
						break;

					case null:
						throw new JSMinException( 'Unterminated comment.' );
					}
				}

			default:
				return $c;
			}
		}

		return $c;
	}

	/**
	 * Get next char. If is ctrl character, translate to a space or newline.
	 *
	 * @uses get()
	 * @return string|null
	 */
	protected function peek() {
		return $this->lookAhead = $this->get();
	}
}

class JSMinException extends Difra\Exception {

}