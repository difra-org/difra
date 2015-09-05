<?php

class EnviUserAgentTest extends PHPUnit_Framework_TestCase
{
    public function test_UA()
    {
        // no user agent string
        \Difra\Envi\UserAgent::setUAString('');
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => false,
                'version' => null,
                'os' => false,
                'engine' => false,
                'device' => null
            ]
        );

        // Safari 6.0 for Mac
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/536.30.1 (KHTML, like Gecko) Version/6.0.5 Safari/536.30.1'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Safari',
                'version' => '6.0',
                'os' => 'Macintosh',
                'engine' => 'WebKit',
                'device' => null
            ]
        );

        // Firefox 21 for Mac
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Firefox',
                'version' => '21.0',
                'os' => 'Macintosh',
                'engine' => 'Gecko',
                'device' => null
            ]
        );

        // Opera 12.15 for Mac
        \Difra\Envi\UserAgent::setUAString(
            'Opera/9.80 (Macintosh; Intel Mac OS X 10.8.4) Presto/2.12.388 Version/12.15'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Opera',
                'version' => '12.15',
                'os' => 'Macintosh',
                'engine' => 'Presto',
                'device' => null
            ]
        );

        // Chrome 27 for Mac
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.110 Safari/537.36'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Chrome',
                'version' => '27.0',
                'os' => 'Macintosh',
                'engine' => 'WebKit',
                'device' => null
            ]
        );

        // Internet Explorer 11
        \Difra\Envi\UserAgent::setUAString('Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko');
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'IE',
                'version' => '11.0',
                'os' => 'Windows',
                'engine' => 'Trident',
                'device' => null
            ]
        );

        // IE 10
        \Difra\Envi\UserAgent::setUAString('Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)');
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'IE',
                'version' => '10.0',
                'os' => 'Windows',
                'engine' => 'Trident',
                'device' => null
            ]
        );

        // IE 9
        \Difra\Envi\UserAgent::setUAString('Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)');
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'IE',
                'version' => '9.0',
                'os' => 'Windows',
                'engine' => 'Trident',
                'device' => null
            ]
        );

        // IE 8
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C)'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'IE',
                'version' => '8.0',
                'os' => 'Windows',
                'engine' => 'Trident',
                'device' => null
            ]
        );

        // IE 7
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.1; WOW64; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; .NET4.0C)'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'IE',
                'version' => '7.0',
                'os' => 'Windows',
                'engine' => false,
                'device' => null
            ]
        );

        // Opera 11.10 for iPad
        \Difra\Envi\UserAgent::setUAString(
            'Opera/9.80 (iPad; Opera Mini/7.0.5/30.3341; U; ru) Presto/2.8.119 Version/11.10'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Opera',
                'version' => '11.10',
                'os' => 'iOS',
                'engine' => 'Presto',
                'device' => 'iPad'
            ]
        );

        // Chrome 27 for iPad
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/5.0 (iPad; CPU OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) CriOS/27.0.1453.10 Mobile/10B329 Safari/8536.25'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Chrome',
                'version' => '27.0',
                'os' => 'iOS',
                'engine' => 'WebKit',
                'device' => 'iPad'
            ]
        );

        // Safari 6 for iPad
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/5.0 (iPad; CPU OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Safari',
                'version' => '6.0',
                'os' => 'iOS',
                'engine' => 'WebKit',
                'device' => 'iPad'
            ]
        );

        // Opera 12.10 for Android Tablet
        \Difra\Envi\UserAgent::setUAString(
            'Opera/9.80 (Android 2.3.7; Linux; Opera Tablet/46223) Presto/2.11.355 Version/12.10'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Opera',
                'version' => '12.10',
                'os' => 'Android',
                'engine' => 'Presto',
                'device' => null
            ]
        );

        // Opera 12.10 for Android Mobile
        \Difra\Envi\UserAgent::setUAString(
            'Opera/9.80 (Android 2.3.7; Linux; Opera Mobi/46223) Presto/2.11.355 Version/12.10'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Opera',
                'version' => '12.10',
                'os' => 'Android',
                'engine' => 'Presto',
                'device' => null
            ]
        );

        // Opera 12.10 for MeeGo
        \Difra\Envi\UserAgent::setUAString(
            'Opera/9.80 (Linux i686; Opera Mobi/46223; MeeGo) Presto/2.11.355 Version/12.10'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Opera',
                'version' => '12.10',
                'os' => 'MeeGo',
                'engine' => 'Presto',
                'device' => null
            ]
        );

        // Opera 12.10 for Linux
        \Difra\Envi\UserAgent::setUAString('Opera/9.80 (X11; Linux zbov) Presto/2.11.355 Version/12.10');
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Opera',
                'version' => '12.10',
                'os' => 'Linux',
                'engine' => 'Presto',
                'device' => null
            ]
        );

        // Opera 12.10 for iPad
        \Difra\Envi\UserAgent::setUAString(
            'Opera/9.80 (Macintosh; Intel Mac OS X 10.8.4; Opera Tablet/46223) Presto/2.11.355 Version/12.10'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Opera',
                'version' => '12.10',
                'os' => 'Macintosh',
                'engine' => 'Presto',
                'device' => null
            ]
        );

        // Android Browser 4.0 for LG-E435 Mobile
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/5.0 (Linux; U; Android 4.1.2; ru-ru; LG-E435 Build/JZO56K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'Android-Browser',
                'version' => '4.0',
                'os' => 'Android',
                'engine' => 'WebKit',
                'device' => null
            ]
        );

        // Blackberry Browser 7.1 for Blackberry 9900
        \Difra\Envi\UserAgent::setUAString(
            'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+'
        );
        $this->assertEquals(
            \Difra\Envi\UserAgent::getUserAgent(),
            [
                'agent' => 'BlackBerry-Browser',
                'version' => '7.1',
                'os' => 'BlackBerry',
                'engine' => 'WebKit',
                'device' => null
            ]
        );
    }
}
