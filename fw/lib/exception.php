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
        self::sendNotification($this);
    }

    /**
     * @static
     * @param \Difra\Exception|\exception $exception
     */
    public static function sendNotification($exception)
    {
        // don't send notifications on development environment
        if (!Envi::isProduction()) {
            return;
        }

        $notificationMail = Config::getInstance()->getValue('email', 'errors');
        // no notification mail is set
        if (!$notificationMail) {
            return;
        }
            $date = date('r');
            $server = print_r($_SERVER, true);
            $post = print_r($_POST, true);
            $cookie = print_r($_COOKIE, true);
            $user = Auth::getInstance()->data['email'];

            $uri = !empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '-';
            $host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '-';

            $exceptionClass = get_class($exception);

            $text = <<<MSG
{$exception->getMessage()}

Page:	$uri
Time:	$date
Host:	$host
Type:   $exceptionClass
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
            mail($notificationMail, $host . ': ' . $exception->getMessage(), $text);
    }
}
