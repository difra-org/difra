<?php

namespace Difra;

/**
 * Class Ajaxer
 * Parses data from Ajaxer.js. Sends action messages to Ajaxer.js.
 * @package Difra
 */
class Ajaxer
{
    /** @var array Non-action responses */
    private static $response = [];
    /** @var array Action responses */
    private static $actions = [];
    /** @var bool Form problem flag */
    private static $problem = false;

    /**
     * Returns ajaxer actions for Ajaxer.js
     * @return string
     */
    public static function getResponse()
    {
        self::setResponse('compatibility', \Difra\Envi\Version::getCompatibility());
        if (Debugger::isConsoleEnabled() !== Debugger::CONSOLE_DISABLED) {
            if (Debugger::hadError()) {
                self::clean(true);
            }
            self::load('#debug', Debugger::debugHTML(false));
        }
        if (!empty(self::$actions)) {
            self::setResponse('actions', self::$actions);
        }
        return json_encode(self::$response, self::getJsonFlags());
    }

    /**
     * Adds ajax response
     * @param string $param Parameter name
     * @param mixed $value Parameter value
     * @return void
     */
    public static function setResponse($param, $value)
    {
        self::$response[$param] = $value;
    }

    /**
     * Clean ajax answer data
     * @param bool $problem
     */
    public static function clean($problem = false)
    {
        self::$actions = [];
        self::$response = [];
        self::$problem = $problem;
    }

    /**
     * Write $html contents to element $target
     * @param string $target jQuery element selector (e.g. '#targetId')
     * @param string $html Content for innerHTML
     * @param bool $replace Force replacing element with $html instead of smart content replace
     */
    public static function load($target, $html, $replace = false)
    {
        self::addAction(
            [
                'action' => 'load',
                'target' => $target,
                'html' => $html,
                'replace' => $replace
            ]
        );
    }

    /**
     * Adds ajaxer action to ajax reply data.
     * @param array $action Ajaxer actions array.
     */
    private static function addAction($action)
    {
        self::$actions[] = $action;
    }

    /**
     * Flags for json_encode() to generate JSON Ajaxer.js can decode
     * @return int
     */
    public static function getJsonFlags()
    {
        static $jsonFlags = null;
        if (!is_null($jsonFlags)) {
            return $jsonFlags;
        }
        $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        if (Debugger::isEnabled()) {
            $jsonFlags |= JSON_PRETTY_PRINT;
        }
        return $jsonFlags;
    }

    /**
     * Returns true if answer contains 'required' or 'invalid' answers.
     * @return bool
     */
    public static function hasProblem()
    {
        return self::$problem;
    }

    /**
     * Display notification message.
     * @param string $message Message text
     */
    public static function notify($message)
    {
        self::addAction(
            [
                'action' => 'notify',
                'message' => htmlspecialchars($message, ENT_IGNORE, 'UTF-8'),
                'lang' => [
                    'close' => Locales::get('notifications/close')
                ]
            ]
        );
    }

    /**
     * Display error message.
     * @param string $message Error message text.
     */
    public static function error($message)
    {
        self::addAction(
            [
                'action' => 'error',
                'message' => htmlspecialchars($message, ENT_IGNORE, 'UTF-8'),
                'lang' => [
                    'close' => Locales::get('notifications/close')
                ]
            ]
        );
        self::$problem = true;
    }

    /**
     * Required field is not filled.
     * Adds .problem class.
     * @param string $name Form field name
     */
    public static function required($name)
    {
        self::$problem = true;
        self::addAction(
            [
                'action' => 'require',
                'name' => $name
            ]
        );
    }

    /**
     * Set incorrect field status for form element
     * @param string $name Form element name
     */
    public static function invalid($name)
    {
        self::$problem = true;
        self::addAction(['action' => 'invalid', 'name' => $name]);
    }

    /**
     * Show status for form element
     * Element should be enclosed in .container element with .status element.
     * HTML sample:
     * <div class="container">
     *        <input name="SomeName" placeholder="Field">
     *        <span class="status">Please fill this field</span>
     * </div>
     * @param string $name Form element name
     * @param string $message Message to display in .status element
     * @param string $class Class name to add to element
     */
    public static function status($name, $message, $class = 'is-invalid')
    {
        self::addAction(
            [
                'action' => 'status',
                'name' => $name,
                'message' => $message,
                'classname' => $class
            ]
        );
    }

    /**
     * Soft refresh current page
     */
    public static function refresh()
    {
        self::redirect($_SERVER['HTTP_REFERER']);
    }

    /**
     * Redirect
     * @param string $url
     * @param bool $reload
     */
    public static function redirect($url, $reload = false)
    {
        self::addAction(
            [
                'action' => 'redirect',
                'url' => $url,
                'reload' => $reload ? 1 : 0
            ]
        );
    }

    /**
     * Reload current page
     */
    public static function reload()
    {
        self::addAction(
            [
                'action' => 'reload'
            ]
        );
    }

    /**
     * Show html content in overlay
     * @param string $html innerHTML content
     * @param string $type type of overlay
     */
    public static function display($html, $type = null)
    {
        self::addAction(
            [
                'action' => 'display',
                'html' => $html,
                'type' => $type
            ]
        );
    }

    const MODAL_SIZE_SM = 'sm';     // 300px
    const MODAL_SIZE_DEFAULT = '';  // 500px
    const MODAL_SIZE_LARGE = 'lg';  // 800px
    const MODAL_SIZE_XL = 'xl';     // 1140px

    /**
     * Show modal window
     * @param string $html  Modal content
     * @param string $size  Modal size
     */
    public static function modal($html, $size = self::MODAL_SIZE_DEFAULT)
    {
        self::addAction(
            [
                'action' => 'modal',
                'html' => $html,
                'size' => $size
            ]
        );
    }

    /**
     * Close overlay
     */
    public static function close()
    {
        self::addAction(
            [
                'action' => 'close'
            ]
        );
    }

    /**
     * Clean form
     */
    public static function reset()
    {
        self::addAction(
            [
                'action' => 'reset'
            ]
        );
    }

    /**
     * Display confirmation window (Are you sure? [Yes] [No])
     * @param $text
     */
    public static function confirm($text)
    {
        self::addAction(
            [
                'action' => 'display',
                'html' =>
                    '<form action="' . Envi::getUri() . '" class="ajaxer">' .
                    '<input type="hidden" name="confirm" value="1"/>' .
                    '<div>' . $text . '</div>' .
                    '<input type="submit" value="' . Locales::get('ajaxer/confirm-yes')
                    . '"/>' .
                    '<input type="button" value="' . Locales::get('ajaxer/confirm-no')
                    . '" onclick="ajaxer.close(this)"/>' .
                    '</form>'
            ]
        );
    }

    /**
     * Execute javascript code.
     * This is dangerous! Don't use it if there is another way.
     * @param $script
     */
    public static function exec($script)
    {
        self::addAction(
            [
                'action' => 'exec',
                'script' => $script
            ]
        );
    }

    /**
     * Add custom action
     * @param $action
     */
    public static function addCustomAction($action)
    {
        self::addAction($action);
    }

    /**
     * Post data (submit form data)
     * @param string $url
     * @param array $data
     */
    public static function post($url, $data)
    {
        self::addAction(
            [
                'action' => 'post',
                'url' => $url,
                'data' => $data
            ]
        );
    }
}
