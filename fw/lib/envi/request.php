<?php

namespace Difra\Envi;

class Request {

	private static $isAjax = false;
	private static $isIframe = false;
	private static $parameters = [];

	public static function isAjax() {

		self::parseRequest();
		return self::$isAjax;
	}

	private static function parseRequest() {

		// parse just once
		static $parsed = false;
		if($parsed) {
			return;
		}
		$parsed = true;

		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
			self::parseAjaxerJSRequest();
		} elseif(isset($_POST['_method']) and $_POST['_method'] == 'iframe') {
			self::parseIframeRequest();
		}
	}

	private static function parseAjaxerJSRequest() {

		self::$isAjax = true;
		$parameters = self::getRequest();
		if(empty($parameters)) {
			return;
		}
		try {
			foreach($parameters as $k => $v) {
				if($k == 'form') {
					foreach($v as $elem) {
						self::parseParam(self::$parameters, $elem['name'], $elem['value']);
					}
				} else {
					self::parseParam(self::$parameters, $k, $v);
				}
			}
		} catch(\Difra\Exception $ex) {
			throw new \Difra\View\Exception(400);
		}
	}

	/**
	 * Get data from ajaxer
	 *
	 * @return array
	 */
	private static function getRequest() {

		$res = [];
		if(!empty($_POST['json'])) {
			$res = json_decode($_POST['json'], true);
		}
		return $res;
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
	private static function parseParam(&$arr, $k, $v) {

		$keys = explode('[', $k);
		if(sizeof($keys) == 1) {
			$arr[$k] = $v;
			return;
		}
		for($i = 1; $i < sizeof($keys); $i++) {
			if($keys[$i]{strlen($keys[$i]) - 1} == ']') {
				$keys[$i] = substr($keys[$i], 0, -1);
			}
		}
		self::putParam($arr, $keys, $v);
	}

	/**
	 * Recursively put parameters to array.
	 * Subroutine for parseParam().
	 *
	 * @param array $arr
	 * @param array $keys
	 * @param mixed $v
	 * @throws \Difra\Exception
	 */
	private static function putParam(&$arr, $keys, $v) {

		if(!is_array($arr)) {
			throw new \Difra\Exception('Ajax->putParam expects array');
		}
		if(empty($keys)) {
			$arr = $v;
			return;
		}
		$k = array_shift($keys);
		if($k) {
			if(!isset($arr[$k])) {
				$arr[$k] = [];
			}
			self::putParam($arr[$k], $keys, $v);
		} else {
			$arr[] = [];
			end($arr);
			self::putParam($arr[key($arr)], $keys, $v);
		}
	}

	private static function parseIframeRequest() {

		self::$isAjax = true;
		self::$isIframe = true;
		self::$parameters = $_POST;
		unset(self::$parameters['method_']);
		if(!empty($_FILES)) {
			foreach($_FILES as $k => $files) {
				if(isset($files['error']) and $files['error'] == UPLOAD_ERR_NO_FILE) {
					continue;
				}
				if(isset($files['name']) and !is_array($files['name'])) {
					self::parseParam(self::$parameters, $k, $files);
					continue;
				}
				if(substr($k, -2) != '[]') {
					$k = $k . '[]';
				}
				if(isset($files['name']) and is_array($files['name'])) {
					$files2 = $files;
					$files = [];
					foreach($files2['name'] as $k2 => $v2) {
						$files[] = [
							'name'     => $v2,
							'type'     => $files2['type'][$k2],
							'tmp_name' => $files2['tmp_name'][$k2],
							'error'    => $files2['error'][$k2],
							'size'     => $files2['size'][$k2]
						];
					}
				}
				foreach($files as $file) {
					if($file['error'] == UPLOAD_ERR_NO_FILE) {
						continue;
					}
					self::parseParam(self::$parameters, $k, $file);
				}
			}
		}
	}

	public static function isIframe() {

		self::parseRequest();
		return self::$isIframe;
	}

	/**
	 * Get parameter value
	 *
	 * @param string $name Parameter name
	 * @return mixed
	 */
	public static function getParam($name) {

		self::parseRequest();
		return isset(self::$parameters[$name]) ? self::$parameters[$name] : null;
	}
}