<?php

namespace Difra;

class Ajaxer {

	private static $response = [];
	private static $actions = [];
	private static $problem = false;


	/**
	 * Singleton
	 *
	 * @return Ajaxer
	 */
	static function getInstance() {

		static $_instance = null;
		return $_instance ? $_instance : $_instance = new self;
	}

	/**
	 * Returns ajaxer actions for execution on browser side.
	 *
	 * @return string
	 */
	public function getResponse() {

		if(Debugger::isEnabled()) {
			if(Debugger::hadError()) {
				$this->clean(true);
			}
			$this->load('#debug', Debugger::debugHTML(false));
		}
		if(!empty(self::$actions)) {
			$this->setResponse('actions', self::$actions);
		}
		return json_encode(self::$response, self::getJsonFlags());
	}

	/**
	 * Clean ajax answer data
	 *
	 * @param bool $problem
	 * @return $this
	 */
	public function clean($problem = false) {

		self::$actions = [];
		self::$response = [];
		self::$problem = $problem;
		return $this;
	}

	/**
	 * Write $html contents to element $target
	 *
	 * @param string $target jQuery element selector (e.g. '#targetId')
	 * @param string $html   Content for innerHTML
	 * @return $this
	 */
	public function load($target, $html) {

		$this->addAction([
			'action' => 'load',
			'target' => $target,
			'html'   => $html
		]);
		return $this;
	}

	/**
	 * Ajaxer Actions
	 */

	/**
	 * Adds ajaxer action to ajax reply data.
	 *
	 * @param array $action Ajaxer actions array.
	 * @return $this
	 */
	private function addAction($action) {

		self::$actions[] = $action;
		return $this;
	}

	/**
	 * Adds ajax reply.
	 *
	 * @param string $param Parameter name
	 * @param mixed  $value Parameter value
	 * @return void
	 */
	public function setResponse($param, $value) {

		self::$response[$param] = $value;
	}

	public static function getJsonFlags() {

		static $jsonFlags = null;
		if(!is_null($jsonFlags)) {
			return $jsonFlags;
		}
		$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
		if(Debugger::isEnabled()) {
			$jsonFlags |= JSON_PRETTY_PRINT;
		}
		return $jsonFlags;
	}

	/**
	 * Returns true if answer contains 'required' or 'invalid' answers.
	 *
	 * @return bool
	 */
	public function hasProblem() {

		return self::$problem;
	}

	/**
	 * Display notification message.
	 *
	 * @param string $message Message text
	 * @return $this
	 */
	public function notify($message) {

		$this->addAction([
			'action'  => 'notify',
			'message' => htmlspecialchars($message, ENT_IGNORE, 'UTF-8'),
			'lang'    => [
				'close' => Locales::get('notifications/close')
			]
		]);
		return $this;
	}

	/**
	 * Display error message.
	 *
	 * @param string $message Error message text.
	 * @return $this
	 */
	public function error($message) {

		$this->addAction([
			'action'  => 'error',
			'message' => htmlspecialchars($message, ENT_IGNORE, 'UTF-8'),
			'lang' => [
				'close' => Locales::get('notifications/close')
			]
		]);
		return $this;
	}

	/**
	 * Required field is not filled.
	 * Adds .problem class.
	 *
	 * @param string $name Form field name
	 * @return $this
	 */
	public function required($name) {

		self::$problem = true;
		$this->addAction([
			'action' => 'require',
			'name'   => $name
		]);
		return $this;
	}

	/**
	 * Set incorrect field status for form element
	 *
	 * @param string $name Form element name
	 * @return $this
	 */
	public function invalid($name) {

		self::$problem = true;
		$action = ['action' => 'invalid', 'name' => $name];
		$this->addAction($action);
		return $this;
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
	 * @return $this
	 */
	public function status($name, $message, $class) {

		$this->addAction([
			'action'    => 'status',
			'name'      => $name,
			'message'   => $message,
			'classname' => $class
		]);
		return $this;
	}

	/**
	 * Soft refresh current page
	 *
	 * @return $this
	 */
	public function refresh() {

		$this->redirect($_SERVER['HTTP_REFERER']);
		return $this;
	}

	/**
	 * Redirect
	 *
	 * @param string $url
	 * @return $this
	 */
	public function redirect($url) {

		$this->addAction([
			'action' => 'redirect',
			'url'    => $url
		]);
		return $this;
	}

	/**
	 * Reload current page
	 *
	 * @return $this
	 */
	public function reload() {

		$this->addAction([
			'action' => 'reload'
		]);
		return $this;
	}

	/**
	 * Show html content in overlay
	 *
	 * @param string $html innerHTML content
	 * @return $this
	 */
	public function display($html) {

		$this->addAction([
			'action' => 'display',
			'html' => $html
		]);
		return $this;
	}

	/**
	 * Close overlay
	 *
	 * @return $this
	 */
	public function close() {

		$this->addAction([
			'action' => 'close'
		]);
		return $this;
	}

	/**
	 * Clean form
	 *
	 * @return $this
	 */
	public function reset() {

		$this->addAction([
			'action' => 'reset'
		]);
		return $this;
	}

	/**
	 * Display confirmation window (Are you sure? [Yes] [No])
	 *
	 * @param $text
	 * @return $this
	 */
	public function confirm($text) {

		$this->addAction([
			'action' => 'display',
			'html'   =>
				'<form action="' . Envi::getUri() . '" class="ajaxer">' .
				'<input type="hidden" name="confirm" value="1"/>' .
				'<div>' . $text . '</div>' .
				'<input type="submit" value="' . Locales::get('ajaxer/confirm-yes')
				. '"/>' .
				'<input type="button" value="' . Locales::get('ajaxer/confirm-no')
				. '" onclick="ajaxer.close(this)"/>' .
				'</form>'
		]);
		return $this;
	}

	/**
	 * Execute javascript code.
	 * This is dangerous! Don't use it if there is another way.
	 *
	 * @param $script
	 * @return $this
	 */
	public function exec($script) {

		$this->addAction([
			'action' => 'exec',
			'script' => $script
		]);
		return $this;
	}
}

