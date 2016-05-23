<?php

namespace Difra\View;

use Difra\Envi;
use Difra\Envi\Version;
use Difra\View;

/**
 * Class HttpError
 * Generates HTTP errors (404, etc.)
 * @package Difra\View
 */
class HttpError extends \Exception
{
    const E_CONTINUE = 100;
    const E_SWITCHING_PROTOCOLS = 101;
    const E_OK = 200;
    const E_CREATED = 201;
    const E_ACCEPTED = 202;
    const E_NON_AUTHORITATIVE_INFORMATION = 203;
    const E_NO_CONTENT = 204;
    const E_RESET_CONTENT = 205;
    const E_PARTIAL_CONTENT = 206;
    const E_MULTIPLE_CHOICES = 300;
    const E_MOVED_PERMANENTLY = 301;
    const E_FOUND = 302;
    const E_SEE_OTHER = 303;
    const E_NOT_MODIFIED = 304;
    const E_USE_PROXY = 305;
    const E_TEMPORARY_REDIRECT = 307;
    const E_BAD_REQUEST = 400;
    const E_UNAUTHORIZED = 401;
    const E_PAYMENT_REQUIRED = 402;
    const E_FORBIDDEN = 403;
    const E_NOT_FOUND = 404;
    const E_METHOD_NOT_ALLOWED = 405;
    const E_NOT_ACCEPTABLE = 406;
    const E_PROXY_AUTHENTICATION_REQUIRED = 407;
    const E_REQUEST_TIMEOUT = 408;
    const E_CONFLICT = 409;
    const E_GONE = 410;
    const E_LENGTH_REQUIRED = 411;
    const E_PRECONDITION_FAILED = 412;
    const E_REQUEST_ENTITY_TOO_LARGE = 413;
    const E_REQUEST_URI_TOO_LONG = 414;
    const E_UNSUPPORTED_MEDIA_TYPE = 415;
    const E_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const E_EXPECTATION_FAILED = 417;
    const E_INTERNAL_SERVER_ERROR = 500;
    const E_NOT_IMPLEMENTED = 501;
    const E_BAD_GATEWAY = 502;
    const E_SERVICE_UNAVAILABLE = 503;
    const E_GATEWAY_TIMEOUT = 504;
    const E_HTTP_VERSION_NOT_SUPPORTED = 505;

    /** @var array HTTP errors */
    public static $errors = [
        self::E_CONTINUE => 'Continue',
        self::E_SWITCHING_PROTOCOLS => 'Switching Protocols',
        self::E_OK => 'OK',
        self::E_CREATED => 'Created',
        self::E_ACCEPTED => 'Accepted',
        self::E_NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::E_NO_CONTENT => 'No Content',
        self::E_RESET_CONTENT => 'Reset Content',
        self::E_PARTIAL_CONTENT => 'Partial Content',
        self::E_MULTIPLE_CHOICES => 'Multiple Choices',
        self::E_MOVED_PERMANENTLY => 'Moved Permanently',
        self::E_FOUND => 'Found',
        self::E_SEE_OTHER => 'See Other',
        self::E_NOT_MODIFIED => 'Not Modified',
        self::E_USE_PROXY => 'Use Proxy',
        self::E_TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::E_BAD_REQUEST => 'Bad Request',
        self::E_UNAUTHORIZED => 'Unauthorized',
        self::E_PAYMENT_REQUIRED => 'Payment Required',
        self::E_FORBIDDEN => 'Forbidden',
        self::E_NOT_FOUND => 'Not Found',
        self::E_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::E_NOT_ACCEPTABLE => 'Not Acceptable',
        self::E_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::E_REQUEST_TIMEOUT => 'Request Timeout',
        self::E_CONFLICT => 'Conflict',
        self::E_GONE => 'Gone',
        self::E_LENGTH_REQUIRED => 'Length Required',
        self::E_PRECONDITION_FAILED => 'Precondition Failed',
        self::E_REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::E_REQUEST_URI_TOO_LONG => 'Request-URI Too Long',
        self::E_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::E_REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::E_EXPECTATION_FAILED => 'Expectation Failed',
        self::E_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::E_NOT_IMPLEMENTED => 'Not Implemented',
        self::E_BAD_GATEWAY => 'Bad Gateway',
        self::E_SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::E_GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::E_HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported'
    ];

    /** @var int|null|string Error */
    public static $error = null;

    /**
     * Construct
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        //parent::__construct( $message, $code, $previous );

        if (isset(self::$errors[$message])) {
            $err = $message;
            $error = self::$errors[$err];
            $msg = '';
        } elseif (isset(self::$errors[$code])) {
            $err = $code;
            $error = self::$errors[$err];
            $msg = $message;
        } else {
            $err = self::E_INTERNAL_SERVER_ERROR;
            $error = self::$errors[$err];
            $msg = $message;
        }

        self::$error = $err;
        header("HTTP/1.1 $err $error");
        /*
        if( $ttl and is_numeric( $ttl ) and $ttl >= 0 ) {
            self::addExpires( $ttl );
        }
        */
        try {
            $xml = new \DOMDocument();
            /** @var $root \DOMElement */
            $root = $xml->appendChild($xml->createElement('error' . $err));
            $root->setAttribute('host', Envi::getSubsite());
            $root->setAttribute('hostname', $host = Envi::getHost());
            $root->setAttribute('mainhost', $mainHost = Envi::getHost(true));
            if ($host != $mainHost) {
                $root->setAttribute('urlprefix', 'http://' . $mainHost);
            }
            $root->setAttribute('build', Version::getBuild());
            $configNode = $root->appendChild($xml->createElement('config'));
            Envi::getStateXML($configNode);
            View::render($xml, 'error_' . $err);
        } catch (\Difra\Exception $ex) {
            echo(<<<ErrorPage
<html>
	<head>
		<title>$error</title>
	</head>
	<body>
		<center>
			<h1 style="padding:350px 0 0 0">Error $err: $error</h1>
			$msg
		</center>
	</body>
</html>
ErrorPage
            );
        }
        View::$rendered = true;
        die();
    }
}
