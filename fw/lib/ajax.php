<?php

namespace Difra;

use Difra\Envi\Action;

class Ajax {

	public $isAjax = false;
	public $isIframe = false;
	public $parameters = array();
	public $response = array();
	private $actions = array();
	private $problem = false;

	/**
	 * Constructor
	 */
	public function __construct() {

		if( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {
			// Ajaxer request
			$this->isAjax = true;
			$parameters = $this->getRequest();
			if( empty( $parameters ) ) {
				return;
			}
			try {
				foreach( $parameters as $k => $v ) {
					if( $k == 'form' ) {
						foreach( $v as $elem ) {
							$this->parseParam( $this->parameters, $elem['name'], $elem['value'] );
						}
					} else {
						$this->parseParam( $this->parameters, $k, $v );
					}
				}
			} catch( Exception $ex ) {
				throw new \Difra\View\Exception( 400 );
			}
		} elseif( isset( $_POST['_method'] ) and $_POST['_method'] == 'iframe' ) {
			// Form came via IFrame
			$this->isAjax = true;
			$this->isIframe = true;
			$this->parameters = $_POST;
			unset( $this->parameters['method_'] );
			if( !empty( $_FILES ) ) {
				foreach( $_FILES as $k => $files ) {
					if( isset( $files['error'] ) and $files['error'] == UPLOAD_ERR_NO_FILE ) {
						continue;
					}
					if( isset( $files['name'] ) and !is_array( $files['name'] ) ) {
						$this->parseParam( $this->parameters, $k, $files );
						continue;
					}
					if( substr( $k, -2 ) != '[]' ) {
						$k = $k . '[]';
					}
					if( isset( $files['name'] ) and is_array( $files['name'] ) ) {
						$files2 = $files;
						$files = array();
						foreach( $files2['name'] as $k2 => $v2 ) {
							$files[] = array(
								'name' => $v2,
								'type' => $files2['type'][$k2],
								'tmp_name' => $files2['tmp_name'][$k2],
								'error' => $files2['error'][$k2],
								'size' => $files2['size'][$k2]
							);
						}
					}
					foreach( $files as $file ) {
						if( $file['error'] == UPLOAD_ERR_NO_FILE ) {
							continue;
						}
						$this->parseParam( $this->parameters, $k, $file );
					}
				}
			}
		}
	}

	/**
	 * Parses parameter and puts it into $arr.
	 * Subroutine for constructor.
	 * Supports parameters like name[abc][]
	 *
	 * @param array  $arr Working array
	 * @param string $k   Parameter key
	 * @param mixed  $v   Parameter value
	 */
	private function parseParam( &$arr, $k, $v ) {

		$keys = explode( '[', $k );
		if( sizeof( $keys ) == 1 ) {
			$arr[$k] = $v;
			return;
		}
		for( $i = 1; $i < sizeof( $keys ); $i++ ) {
			if( $keys[$i]{strlen( $keys[$i] ) - 1} == ']' ) {
				$keys[$i] = substr( $keys[$i], 0, -1 );
			}
		}
		$this->putParam( $arr, $keys, $v );
	}

	/**
	 * Recursively put parameters to array.
	 * Subroutine for parseParam().
	 *
	 * @param array $arr
	 * @param array $keys
	 * @param mixed $v
	 *
	 * @throws Exception
	 */
	private function putParam( &$arr, $keys, $v ) {

		if( !is_array( $arr ) ) {
			throw new Exception( 'Ajax->putParam expects array' );
		}
		if( empty( $keys ) ) {
			$arr = $v;
			return;
		}
		$k = array_shift( $keys );
		if( $k ) {
			if( !isset( $arr[$k] ) ) {
				$arr[$k] = array();
			}
			$this->putParam( $arr[$k], $keys, $v );
		} else {
			$arr[] = array();
			end( $arr );
			$this->putParam( $arr[key( $arr )], $keys, $v );
		}
	}

	/**
	 * Singleton
	 *
	 * @return Ajax
	 */
	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Get data from ajaxer
	 *
	 * @return array
	 */
	private function getRequest() {

		$res = array();
		if( !empty( $_POST['json'] ) ) {
			$res = json_decode( $_POST['json'], true );
		}
		return $res;
	}

	/**
	 * Get parameter value
	 *
	 * @param string $name Parameter name
	 *
	 * @return mixed
	 */
	public function getParam( $name ) {

		return isset( $this->parameters[$name] ) ? $this->parameters[$name] : null;
	}

	/**
	 * Adds ajax reply.
	 *
	 * @param string $param Parameter name
	 * @param mixed  $value Parameter value
	 *
	 * @return void
	 */
	public function setResponse( $param, $value ) {

		$this->response[$param] = $value;
	}

	/**
	 * Returns ajaxer actions for execution on browser side.
	 *
	 * @return string
	 */
	public function getResponse() {

		if( Debugger::isEnabled() ) {
			if( Debugger::hadError() ) {
				$this->clean( true );
			}
			$this->load( '#debug', Debugger::debugHTML( false ) );
		}
		if( !empty( $this->actions ) ) {
			$this->setResponse( 'actions', $this->actions );
		}
		return json_encode( $this->response, self::getJsonFlags() );
	}

	public static function getJsonFlags() {

		static $jsonFlags = null;
		if( !is_null( $jsonFlags ) ) {
			return $jsonFlags;
		}
		$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
		if( Debugger::isEnabled() ) {
			$jsonFlags |= JSON_PRETTY_PRINT;
		}
		return $jsonFlags;
	}

	/**
	 * Ajaxer Actions
	 */

	/**
	 * Adds ajaxer action to ajax reply data.
	 *
	 * @param array $action Ajaxer actions array.
	 *
	 * @return void
	 */
	private function addAction( $action ) {

		$this->actions[] = $action;
	}

	/**
	 * Returns true if answer contains 'required' or 'invalid' answers.
	 *
	 * @return bool
	 */
	public function hasProblem() {

		return $this->problem;
	}

	/**
	 * Clean ajax answer data
	 *
	 * @param bool $problem
	 */
	public function clean( $problem = false ) {

		$this->actions = array();
		$this->response = array();
		$this->problem = $problem;
	}

	/**
	 * Display notification message.
	 *
	 * @param string $message Message text
	 *
	 * @return void
	 */
	public function notify( $message ) {

		$this->addAction( array(
				       'action' => 'notify',
				       'message' => htmlspecialchars( $message, ENT_IGNORE, 'UTF-8' ),
				       'lang' => array(
					       'close' => Locales::getInstance()->getXPath( 'notifications/close' )
				       )
				  ) );
	}

	/**
	 * Display error message.
	 *
	 * @param string $message Error message text.
	 *
	 * @return void
	 */
	public function error( $message ) {

		$this->addAction( array(
				       'action' => 'error',
				       'message' => htmlspecialchars( $message, ENT_IGNORE, 'UTF-8' ),
				       'lang' => array(
					       'close' => Locales::getInstance()->getXPath( 'notifications/close' )
				       )
				  ) );
	}

	/**
	 * Required field is not filled.
	 * Adds .problem class.
	 *
	 * @param string $name Form field name
	 *
	 * @return void
	 */
	public function required( $name ) {

		$this->problem = true;
		$this->addAction( array(
				       'action' => 'require',
				       'name' => $name
				  ) );
	}

	/**
	 * Set incorrect field status for form element
	 *
	 * @param string $name    Form element name
	 *
	 * @return void
	 */
	public function invalid( $name ) {

		$this->problem = true;
		$action = array( 'action' => 'invalid', 'name' => $name );
		$this->addAction( $action );
	}

	/**
	 * Show status for form element
	 * Element should be enclosed in .container element with .status element.
	 * HTML sample:
	 * <div class="container">
	 *        <input name="SomeName" placeholder="Field">
	 *        <span class="status">Please fill this field</span>
	 * </div>
	 *
	 * @param string $name    Form element name
	 * @param string $message Message to display in .status element
	 * @param string $class   Class name to add to element
	 *
	 * @return void
	 */
	public function status( $name, $message, $class ) {

		$this->addAction( array(
				       'action' => 'status',
				       'name' => $name,
				       'message' => $message,
				       'classname' => $class
				  ) );
	}

	/**
	 * Redirect
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	public function redirect( $url ) {

		$this->addAction( array(
				       'action' => 'redirect',
				       'url' => $url
				  ) );
	}

	/**
	 * Soft refresh current page
	 */
	public function refresh() {

		$this->redirect( $_SERVER['HTTP_REFERER'] );
	}

	/**
	 * Reload current page
	 *
	 * @return void
	 */
	public function reload() {

		$this->addAction( array(
				       'action' => 'reload'
				  ) );
	}

	/**
	 * Show html content in overlay
	 *
	 * @param string $html innerHTML content
	 *
	 * @return void
	 */
	public function display( $html ) {

		$this->addAction( array(
				       'action' => 'display',
				       'html' => $html
				  ) );
	}

	/**
	 * Write $html contents to element $target
	 *
	 * @param string $target jQuery element selector (e.g. '#targetId')
	 * @param string $html   Content for innerHTML
	 *
	 * @return void
	 */
	public function load( $target, $html ) {

		$this->addAction( array(
				       'action' => 'load',
				       'target' => $target,
				       'html' => $html
				  ) );
	}

	/**
	 * Close overlay
	 */
	public function close() {

		$this->addAction( array(
				       'action' => 'close'
				  ) );
	}

	/**
	 * Clean form
	 */
	public function reset() {

		$this->addAction( array(
				       'action' => 'reset'
				  ) );
	}

	/**
	 * Display confirmation window (Are you sure? [Yes] [No])
	 *
	 * @param $text
	 */
	public function confirm( $text ) {

		$this->addAction( array(
				       'action' => 'display',
				       'html' =>
				       '<form action="' . Envi::getUri() . '" class="ajaxer">' .
				       '<input type="hidden" name="confirm" value="1"/>' .
				       '<div>' . $text . '</div>' .
				       '<input type="submit" value="' . Locales::getInstance()->getXPath( 'ajaxer/confirm-yes' )
				       . '"/>' .
				       '<input type="button" value="' . Locales::getInstance()->getXPath( 'ajaxer/confirm-no' ) . '" onclick="ajaxer.close(this)"/>' .
				       '</form>'
				  ) );
	}

	/**
	 * @param $script
	 */
	public function exec( $script ) {

		$this->addAction( array(
				       'action' => 'exec',
				       'script' => $script
				  ) );
	}
}

