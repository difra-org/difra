<?php

namespace Difra\Envi;

use Difra\Exception;
use Difra\View\Exception as ViewException;

/**
 * Class Request
 * Processes complex requests, e.g. requests made with Ajaxer.js or forms submitted via iframe.
 *
 * @package Difra\Envi
 */
class Request
{
    private static $isAjax = false;
    private static $isIframe = false;
    private static $parameters = [];

    /**
     * Is current request comes by Ajaxer.js?
     *
     * @return bool
     */
    public static function isAjax()
    {
        self::parseRequest();
        return self::$isAjax;
    }

    /**
     * Find out request type and call proper parser
     *
     * @throws \Difra\View\Exception
     */
    private static function parseRequest()
    {
        // parse just once
        static $parsed = false;
        if ($parsed) {
            return;
        }
        $parsed = true;

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
            self::parseAjaxerJSRequest();
        } elseif (isset($_POST['_method']) and $_POST['_method'] == 'iframe') {
            self::parseIframeRequest();
        }
    }

    /**
     * Parser for Ajaxer.js requests
     *
     * @throws \Difra\View\Exception
     */
    private static function parseAjaxerJSRequest()
    {
        self::$isAjax = true;
        $parameters = self::getRequest();
        if (empty($parameters)) {
            return;
        }
        try {
            foreach ($parameters as $k => $v) {
                if ($k == 'form') {
                    foreach ($v as $elem) {
                        self::parseParam(self::$parameters, $elem['name'], $elem['value']);
                    }
                } else {
                    self::parseParam(self::$parameters, $k, $v);
                }
            }
        } catch (Exception $ex) {
            throw new ViewException(400);
        }
    }

    /**
     * Get data from ajaxer
     *
     * @return array
     */
    private static function getRequest()
    {
        $res = [];
        if (!empty($_POST['json'])) {
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
    private static function parseParam(&$arr, $k, $v)
    {
        $keys = explode('[', $k);
        if (sizeof($keys) == 1) {
            $arr[$k] = $v;
            return;
        }
        for ($i = 1; $i < sizeof($keys); $i++) {
            if ($keys[$i]{strlen($keys[$i]) - 1} == ']') {
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
     * @throws Exception
     */
    private static function putParam(&$arr, $keys, $v)
    {
        if (!is_array($arr)) {
            throw new Exception('Ajax->putParam expects array');
        }
        if (empty($keys)) {
            $arr = $v;
            return;
        }
        $k = array_shift($keys);
        if ($k) {
            if (!isset($arr[$k])) {
                $arr[$k] = [];
            }
            self::putParam($arr[$k], $keys, $v);
        } else {
            $arr[] = [];
            end($arr);
            self::putParam($arr[key($arr)], $keys, $v);
        }
    }

    /**
     * Parser for form submitted via iframe
     */
    private static function parseIframeRequest()
    {
        self::$isAjax = true;
        self::$isIframe = true;
        self::$parameters = $_POST;
        unset(self::$parameters['method_']);
        if (!empty($_FILES)) {
            foreach ($_FILES as $k => $files) {
                if (isset($files['error']) and $files['error'] == UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if (isset($files['name']) and !is_array($files['name'])) {
                    self::parseParam(self::$parameters, $k, $files);
                    continue;
                }
                if (substr($k, -2) != '[]') {
                    $k = $k . '[]';
                }
                if (isset($files['name']) and is_array($files['name'])) {
                    $files2 = $files;
                    $files = [];
                    foreach ($files2['name'] as $k2 => $v2) {
                        $files[] = [
                            'name'     => $v2,
                            'type'     => $files2['type'][$k2],
                            'tmp_name' => $files2['tmp_name'][$k2],
                            'error'    => $files2['error'][$k2],
                            'size'     => $files2['size'][$k2]
                        ];
                    }
                }
                foreach ($files as $file) {
                    if ($file['error'] == UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    self::parseParam(self::$parameters, $k, $file);
                }
            }
        }
    }

    /**
     * Is request a form parsed via iframe?
     *
     * @return bool
     */
    public static function isIframe()
    {
        self::parseRequest();
        return self::$isIframe;
    }

    /**
     * Get parameter value
     *
     * @param string $name Parameter name
     * @return mixed
     */
    public static function getParam($name)
    {
        self::parseRequest();
        return isset(self::$parameters[$name]) ? self::$parameters[$name] : null;
    }
}
