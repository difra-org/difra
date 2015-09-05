<?php

namespace Difra;

/**
 * Class Exception
 * @package Difra
 */
class Exception extends \exception
{
    /**
     * Wrapper for sending e-mails about exceptions which should never happen.
     * Just call $exception->notify() in catch section.
     */
    public function notify()
    {
        self::notifyObj($this);
    }

    /**
     * @static
     * @param \Difra\Exception|\exception $exception
     */
    private static function notifyObj($exception = null)
    {
        if (Envi::getMode() == 'web' and !Debugger::isConsoleEnabled()) {
            $date = date('r');
            $server = print_r($_SERVER, true);
            $post = print_r($_POST, true);
            $cookie = print_r($_COOKIE, true);
            $user = Auth::getInstance()->data['email'];

            $uri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '-';
            $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '-';

            $text = <<<MSG
{$exception->getMessage()}

Page:	$uri
Time:	$date
Host:	$host
File:	{$exception->getFile()}
Line:	{$exception->getLine()}
User:	$user

{$exception->getTraceAsString()}

\$_SERVER:
$server

\$_POST:
$post

\$_COOKIE:
$cookie
MSG;
            // TODO: move exceptions e-mail address to configuration
            mail('errors@ajamstudio.com', $host . ': ' . $exception->getMessage(), $text);
        } else {
        }
    }
}
