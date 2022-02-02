<?php

declare(strict_types=1);

namespace Difra;

/**
 * Class Ajaxer
 * Parses data from Ajaxer.js. Sends action messages to Ajaxer.js.
 * @package Difra
 */
class Ajaxer
{
    /** @var array Non-action responses */
    private static array $response = [];
    /** @var array Action responses */
    private static array $actions = [];
    /** @var bool Form problem flag */
    private static bool $problem = false;

    /**
     * Returns ajaxer actions for Ajaxer.js
     * @return string
     */
    public static function getResponse(): string
    {
        if (Debugger::isConsoleEnabled() !== Debugger::CONSOLE_DISABLED) {
            if (Debugger::hadError()) {
                self::clean(true);
            }
            self::load('#debug', Debugger::debugHTML());
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
     */
    public static function setResponse(string $param, mixed $value)
    {
        self::$response[$param] = $value;
    }

    /**
     * Clean ajax answer data
     * @param bool $problem
     */
    public static function clean(bool $problem = false): void
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
    public static function load(string $target, string $html, bool $replace = false): void
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
    private static function addAction(array $action): void
    {
        self::$actions[] = $action;
    }

    /**
     * Flags for json_encode() to generate JSON Ajaxer.js can decode
     * @return int
     */
    public static function getJsonFlags(): int
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
    public static function hasProblem(): bool
    {
        return self::$problem;
    }

    /**
     * Display notification message.
     * @param string $message Message text
     * @throws \Difra\Exception
     */
    public static function notify(string $message): void
    {
        self::addAction(
            [
                'action' => 'notify',
                'message' => htmlspecialchars($message, ENT_IGNORE),
                'lang' => [
                    'close' => Locales::get('notifications/close')
                ]
            ]
        );
    }

    /**
     * Display error message.
     * @param string $message Error message text.
     * @throws \Difra\Exception
     */
    public static function error(string $message): void
    {
        self::addAction(
            [
                'action' => 'error',
                'message' => htmlspecialchars($message, ENT_IGNORE),
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
    public static function required(string $name): void
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
    public static function invalid(string $name): void
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
    public static function status(string $name, string $message, string $class = 'is-invalid'): void
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
    public static function redirect(string $url, bool $reload = false): void
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
    public static function reload(): void
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
     * @param string|null $type type of overlay
     */
    public static function display(string $html, ?string $type = null): void
    {
        self::addAction(
            [
                'action' => 'display',
                'html' => $html,
                'type' => $type
            ]
        );
    }

    public const MODAL_SIZE_SM = 'sm';     // 300px
    public const MODAL_SIZE_DEFAULT = '';  // 500px
    public const MODAL_SIZE_LARGE = 'lg';  // 800px
    public const MODAL_SIZE_XL = 'xl';     // 1140px

    /**
     * Show modal window
     * @param string $html  Modal content
     * @param string $size  Modal size
     */
    public static function modal(string $html, string $size = self::MODAL_SIZE_DEFAULT): void
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
    public static function close(): void
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
    public static function reset(): void
    {
        self::addAction(
            [
                'action' => 'reset'
            ]
        );
    }

    /**
     * Display confirmation window (Are you sure? [Yes] [No])
     * @param string $text
     * @throws \Difra\Exception
     */
    public static function confirm(string $text): void
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
     * @param string $script
     */
    public static function exec(string $script): void
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
     * @param array $action
     */
    public static function addCustomAction(array $action): void
    {
        self::addAction($action);
    }

    /**
     * Post data (submit form data)
     * @param string $url
     * @param array $data
     */
    public static function post(string $url, array $data)
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
