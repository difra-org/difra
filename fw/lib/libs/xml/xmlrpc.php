<?php

namespace Difra\Libs\XML;

/**
 * Class XMLRPC
 * XML-RPC implementation
 *
 * @package Difra\Libs\XML
 */
class XMLRPC
{
    /**
     * Singleton
     *
     * @static
     * @return self
     */
    public static function getInstance()
    {
        static $_instance = null;
        if (!$_instance) {
            $_instance = new self;
        }
        return $_instance;
    }

    /**
     * Send request
     * In case of error returns: [ "faultString" => "Server error: method not found.", "faultCode" => -32601 ]
     *
     * @param string $url
     * @param string $method
     * @param array  $params
     * @return mixed
     */
    public function sendRequest($url, $method, $params)
    {
        // assemble POST body
        $request = xmlrpc_encode_request($method, $params);

        // make request
        $cl = curl_init($url);
        curl_setopt($cl, CURLOPT_CRLF, 1);
        curl_setopt($cl, CURLOPT_POST, 1);
        curl_setopt($cl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec($cl);

        // decode answer
        $result = xmlrpc_decode($contents);

        return $result;
    }

    /**
     * Request processor
     *
     * @param object $handler
     * @param array|bool $methodsList If omitted, handler should contain getMethods() method
     * @return string
     */
    public function processRequest($handler, $methodsList = false)
    {
        $server = xmlrpc_server_create();

        if (!$methodsList) {
            $methodsList = $handler->getMethods();
        }
        foreach ($methodsList as $method) {
            xmlrpc_server_register_method($server, $method, [$handler, $method]);
        }
        $request = (isset ($HTTP_RAW_POST_DATA) and $HTTP_RAW_POST_DATA)
            ? $HTTP_RAW_POST_DATA
            : file_get_contents(
                'php://input'
            );

        $response = xmlrpc_server_call_method($server, $request, null);
        xmlrpc_server_destroy($server);
        return $response;
    }
}
