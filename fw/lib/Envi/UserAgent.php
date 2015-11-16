<?php

namespace Difra\Envi;

use Difra\Libs\XML\DOM;

/**
 * Class UserAgent
 * @package Difra\Envi
 */
class UserAgent
{
    /** @var array Browser user agents */
    private static $agents = [
        'OPR' => 'Opera',
        'Chrome' => 'Chrome',
        'CriOS' => 'Chrome',
        'Firefox' => 'Firefox',
        'Opera' => 'Opera',
        'Safari' => 'Safari',
        'Trident' => 'IE'
    ];
    /** @var array Browser engines */
    private static $engines = [
        'AppleWebKit' => 'WebKit',
        'Gecko' => 'Gecko',
        'Presto' => 'Presto',
        'Trident' => 'Trident'
    ];
    /** @var array Browser operating systems */
    private static $oses = [
        'Windows' => 'Windows',
        'Macintosh' => 'Macintosh',
        'iPad' => 'iOS',
        'iPod' => 'iOS',
        'iPhone' => 'iOS',
        'Android' => 'Android',
        'BlackBerry' => 'BlackBerry',
        'MeeGo' => 'MeeGo',
        'Linux' => 'Linux'
    ];

    /**
     * Get user agent information as XML
     * @param \DOMElement $node
     */
    public static function getUserAgentXML($node)
    {
        if ($ua = self::getUserAgent()) {
            DOM::array2domAttr($node, $ua);
        }
        if ($uac = self::getUserAgentClass()) {
            $node->setAttribute('uaClass', $uac);
        }
    }

    /** @var string User agent information */
    private static $userAgent = null;

    /**
     * Get user agent information as array
     * @return array
     */
    public static function getUserAgent()
    {
        if (!is_null(self::$userAgent)) {
            return self::$userAgent;
        }
        return self::$userAgent = [
            'agent' => self::getAgent(),
            'version' => self::getVersion(),
            'os' => self::getOS(),
            'engine' => self::getEngine(),
            'device' => self::getDevice()
        ];
    }

    /** @var string User agent identifier */
    private static $agentId = null;

    /**
     * Get user agent identifier
     * @return string|bool
     */
    public static function getAgentId()
    {
        if (!is_null(self::$agentId)) {
            return self::$agentId;
        }
        $ua = self::getUAArray();
        foreach (self::$agents as $agent => $aName) {
            if (isset($ua[$agent])) {
                return self::$agentId = $agent;
            }
        }
        return self::$agentId = false;
    }

    /** @var string User agent  */
    private static $agent = null;

    /**
     * Get user agent name
     * @return string|bool
     */
    public static function getAgent()
    {
        if (!is_null(self::$agent)) {
            return self::$agent;
        }
        $agentId = self::getAgentId();
        $ua = self::getUAArray();
        $os = self::getOS();
        if ($os == 'Android' and $agentId == 'Safari' and strpos($ua['Version'], 'Mobile') !== false) {
            return self::$agent = 'Android-Browser';
        }
        if ($os == 'BlackBerry' and $agentId == 'Safari' and strpos($ua['Version'], 'Mobile') !== false) {
            return self::$agent = 'BlackBerry-Browser';
        }
        if ($agentId and isset(self::$agents[$agentId])) {
            return self::$agent = self::$agents[$agentId];
        }
        if (isset($ua['Mozilla']) and strpos($ua['Mozilla'], 'MSIE')) {
            return self::$agentId = 'IE';
        }
        return self::$agent = false;
    }

    /** @var string Browser engine */
    private static $engine = null;

    /**
     * Get user agent engine name
     * @return string|bool
     */
    public static function getEngine()
    {
        if (!is_null(self::$engine)) {
            return self::$engine;
        }
        $ua = self::getUAArray();
        foreach (self::$engines as $engine => $eName) {
            if (isset($ua[$engine])) {
                return self::$engine = $eName;
            }
        }
        return self::$engine = false;
    }

    /** @var string Operating system */
    private static $os = null;
    /** @var string Raw OS (?) */
    private static $rawOS = null;

    /**
     * Get user agent OS
     * @return string|bool
     */
    public static function getOS()
    {
        if (!is_null(self::$os)) {
            return self::$os;
        }
        $uaString = self::getUAString();
        foreach (self::$oses as $os => $osName) {
            if (strpos($uaString, $os)) {
                self::$rawOS = $os;
                return self::$os = $osName;
            }
        }
        return self::$os = false;
    }

    /** @var string Browser version */
    private static $version = null;

    /**
     * Get user agent version
     * @return string|bool
     */
    public static function getVersion()
    {
        if (!is_null(self::$version)) {
            return self::$version;
        }
        $ua = self::getUAArray();
        $agent = self::getAgent();
        $agentId = self::getAgentId();
        if (isset($ua['Version'])) { // good browsers that give us version
            self::$version = $ua['Version'];
            if (substr(self::$version, -7) == ' Mobile') {
                self::$version = substr(self::$version, 0, -7);
            }
        } elseif (isset($ua[$agentId])) {
            if ($msiePos = strpos($ua['Mozilla'], 'MSIE')) { // IE 8-10
                $version = substr($ua['Mozilla'], $msiePos + 4);
                if ($p = strpos($version, ';')) {
                    $version = substr($version, 0, $p);
                }
                self::$version = trim($version);
            } elseif ($agentId == 'Trident') { // IE 11
                $rv1 = strpos($ua['Trident'], 'rv:') + 3;
                $rv2 = strpos($ua['Trident'], ')', $rv1);
                self::$version = substr($ua['Trident'], $rv1, $rv2 - $rv1);
            } elseif ($agentId == 'Opera') { // Opera
                self::$version = explode(' ', $ua[$agentId], 2)[0];
            } else {
                self::$version = $ua[$agentId];
            }
        } elseif ($agent == 'IE') { // IE 7
            $version = substr($ua['Mozilla'], strpos($ua['Mozilla'], 'MSIE') + 4);
            if ($p = strpos($version, ';')) {
                $version = substr($version, 0, $p);
            }
            self::$version = trim($version);
        }
        if (sizeof($vv = explode('.', self::$version, 3)) >= 2) {
            self::$version = $vv[0] . '.' . $vv[1];
        }
        return self::$version;
    }

    /** @var string User agent string */
    private static $uaString = null;

    /**
     * Get user agent header
     * @return string|bool
     */
    public static function getUAString()
    {
        if (!is_null(self::$uaString)) {
            return self::$uaString;
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return self::$uaString = $_SERVER['HTTP_USER_AGENT'];
        }
        return self::$uaString = false;
    }

    /** @var array User agent array */
    private static $uaArray = null;

    /**
     * Get hash array from User-Agent string
     * @return array
     */
    private static function getUAArray()
    {
        if (!is_null(self::$uaArray)) {
            return self::$uaArray;
        }
        $ua = [];
        preg_match_all('/([^\/]+)\/([^\/]+)(\s|$)/', self::getUAString(), $ua1);
        foreach ($ua1[1] as $k => $v) {
            $ua[$ua1[1][$k]] = $ua1[2][$k];
        }
        return self::$uaArray = $ua;
    }

    /** @var string User agent CSS class */
    private static $uaClass = null;

    /**
     * Get CSS classes for user agent
     * @return string
     */
    public static function getUserAgentClass()
    {
        if (!is_null(self::$uaClass)) {
            return self::$uaClass;
        }
        $a = self::getUserAgent();
        $uac = [];
        if ($a['agent']) {
            $uac[] = $a['agent'];
        }
        if ($a['version']) {
            $uac[] = 'v' . intval($a['version']);
            $uac[] = 'vv' . str_replace(['.', ' '], '_', $a['version']);
        }
        if ($a['os']) {
            $uac[] = $a['os'];
        }
        if ($a['engine']) {
            $uac[] = $a['engine'];
        }
        return self::$uaClass = trim(implode(' ', $uac));
    }

    /** @var string Browser host device type */
    private static $device = null;

    /**
     * Get user agent device name
     * @return string
     */
    public static function getDevice()
    {
        if (!is_null(self::$device)) {
            return self::$device;
        }
        self::getOS();
        if (in_array(self::$rawOS, ['iPhone', 'iPad', 'iPod'])) {
            return self::$device = self::$rawOS;
        }
        return self::$device;
    }

    /**
     * Set custom user agent string
     * Used by unit tests.
     * @param $string
     */
    public static function setUAString($string)
    {
        self::$uaString = $string;
        self::$userAgent = null;
        self::$agent = null;
        self::$agentId = null;
        self::$version = null;
        self::$engine = null;
        self::$os = null;
        self::$uaArray = null;
        self::$uaClass = null;
        self::$device = null;
    }
}
