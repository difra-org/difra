<?php

namespace Difra\Mailer;

use Difra\Envi;
use Difra\Exception;
use Difra\Locales;
use Difra\View;

/**
 * Class Common
 * @package Drafton\Mailer
 */
abstract class Common
{
    const EOL = "\r\n";
    /** @var string|[string,string] From address [string mail,string name] */
    protected $from = [];
    /** @var [string,string][] From addresses array */
    protected $to = [];
    /** @var [string,string][] CC addresses array */
    protected $cc = [];
    /** @var [string,string][] BCC addresses array */
    protected $bcc = [];
    /** @var string Subject */
    protected $subject = '';
    /** @var string Body */
    protected $body = '';
    /** @var string[] Additional headers */
    protected $headers = [];

    /**
     * Send mail
     * @return mixed
     */
    abstract public function send();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->from = 'noreply@' . Envi::getHost(true);
    }

    /**
     * Load configuration
     * @param array $config
     */
    public function loadConfig($config)
    {
        if (!empty($config['from'])) {
            $this->setFrom($config['from']);
        }
    }

    /**
     * Set From address
     * @param string|array $address
     */
    public function setFrom($address)
    {
        $this->from = $this->makeAddress($address);
    }

    /**
     * Get headers
     * @param bool $implode
     * @param bool $full
     * @return string|\string[]
     */
    protected function getHeaders($implode = false, $full = false)
    {
        $from = $this->formatFrom();
        $to = $this->formatTo();
        $headers = array_merge([
            "Mime-Version: 1.0",
            "Content-Type: text/html; charset=\"UTF-8\"",
            "Date: " . date('r'),
            "Message-Id: <" . md5(microtime()) . '-' . md5($from . implode('', $to)) . '@' . Envi::getHost(true) . '>',
            'Content-Transfer-Encoding: 8bit',
            "From: $from"
        ], $this->headers);
        if ($full) {
            foreach ($this->formatTo() as $to) {
                $headers[] = "To: $to";
            }
            $headers[] = "Subject: " . $this->formatSubject();
        }
        return $implode ? implode(self::EOL, $headers) : $headers;
    }

    /**
     * Add additional header
     * @param string $header
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    /**
     * Clean additional headers
     */
    public function cleanHeaders()
    {
        $this->headers = [];
    }

    /**
     * Make address record from mail and name
     * @param string|array $address
     * @param bool $onlyMail
     * @return mixed
     * @throws Exception
     */
    protected function formatAddress($address, $onlyMail = false)
    {
        if (!is_array($address)) {
            return $address;
        } elseif (!isset($address[0])) {
            throw new Exception('Mailer::formatAddress got unexpected input');
        } elseif (empty($address[1]) or $onlyMail) {
            return $address[0];
        } elseif (preg_match('/[\\x80-\\xff]+/', $address[1])) {
            return '=?utf-8?B?' . base64_encode($address[1]) . "==?= <{$address[0]}>";
        } else {
            return "{$address[1]} <{$address[0]}>";
        }
    }

    /**
     * Add To address
     * @param string|array $address
     * @throws Exception
     */
    public function setTo($address)
    {
        $this->cleanTo();
        $this->to[] = $this->makeAddress($address);
    }

    /**
     * Add To address
     * @param string|array $address
     * @throws Exception
     */
    public function addTo($address)
    {
        $this->to[] = $this->makeAddress($address);
    }

    /**
     * Clean To addresses
     */
    public function cleanTo()
    {
        $this->to = [];
    }

    /**
     * Make address from string|array
     * @param string|array $address
     * @return array
     * @throws Exception
     */
    protected function makeAddress($address)
    {
        if (!is_array($address)) {
            return [$address];
        } elseif (!isset($address[0])) {
            throw new Exception('Mailer::makeAddress got unexpected input');
        } elseif (empty($address[1])) {
            return [$address[0]];
        } else {
            return [$address[0], $address[1]];
        }
    }

    /**
     * Get formatted To list
     * @param bool $onlyMail
     * @return \string[]
     */
    protected function formatTo($onlyMail = false)
    {
        $res = [];
        foreach ($this->to as $to) {
            $res[] = $this->formatAddress($to, $onlyMail);
        }
        return $res;
    }

    /**
     * Get formatted From string
     * @param bool $onlyMail
     * @return string
     */
    protected function formatFrom($onlyMail = false)
    {
        return $this->formatAddress($this->from, $onlyMail);
    }

    /**
     * Get formatted Subject string
     * @param string|null $subject
     * @return string
     */
    public function formatSubject($subject = null)
    {
        if (!$subject) {
            $subject = $this->subject;
        }
        if (!preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $subject, $matches)) {
            return $subject;
        }

        $mb_length = mb_strlen($subject);
        $length = 63;
        $avgLength = floor($length * ($mb_length / strlen($subject)) * .75);
        $encoded = [];
        for ($i = 0; $i < $mb_length; $i += $offset) {
            $lookBack = 0;
            do {
                $offset = $avgLength - $lookBack;
                $chunk = mb_substr($subject, $i, $offset);
                $chunk = base64_encode($chunk);
                $lookBack++;
            } while (strlen($chunk) > $length);
            $encoded[] = $chunk;
        }
        return '=?utf-8?B?' . implode("?=\n =?utf-8?B?", $encoded) . '?=';
    }

    /**
     * Set mail subject
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Set mail body
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get formatted body
     * @param bool $binary
     * @return string
     */
    protected function formatBody($binary = false)
    {
        $body = $this->body;
        // escape dots when it's line first character
        if (!$binary) {
            $chunks = mb_split('\r\n\.', $body);
            if (sizeof($chunks) > 1) {
                $body = implode("\r\n..", $chunks);
            }
        }
        return $body;
    }

    /**
     * Generate and send e-mail message
     * Data are passed to templates as <mail> node attributes.
     * Message template can contain following tags: from, fromtext, subject, text
     * @param string $to
     * @param string $template
     * @param array $data
     * @deprecated
     */
    public function createMail($to, $template, $data)
    {
        // render template
        $xml = new \DOMDocument();
        /** @var \DOMelement $root */
        $root = $xml->appendChild($xml->createElement('mail'));
        $root->setAttribute('host', Envi::getHost(true));
        Locales::getInstance()->getLocaleXML($root);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $root->setAttribute($k, $v);
            }
        }
        $templateText = View::render($xml, $template, true);

        $this->setTo($to);

        // get template strings
        if (empty($this->subject)) {
            preg_match('|<subject[^>]*>(.*)</subject>|Uis', $templateText, $subject);
            if (!empty($subject[1])) {
                $this->setSubject($subject[1]);
            }
        }
        if (empty($this->from)) {
            preg_match('|<from[^>]*>(.*)</from>|Uis', $templateText, $fromMail);
            $fromMail = !empty($fromMail[1]) ? $fromMail[1] : null;
            preg_match('|<fromtext[^>]*>(.*)</fromtext>|Uis', $templateText, $fromText);
            $fromText = !empty($fromText[1]) ? $fromText[1] : null;
            if (!empty($fromMail[1])) {
                $this->setFrom([$fromMail[1], !empty($fromText[1]) ? $fromText[1] : null]);
            }
        }
        preg_match('|<text[^>]*>(.*)</text>|Uis', $templateText, $mailText);
        $this->setBody(!empty($mailText[1]) ? $mailText[1] : $templateText);

        $this->send();
    }

    /**
     * Render message body from template
     * @param string $template
     * @param array $data
     */
    public function render($template, $data)
    {
        $xml = new \DOMDocument();
        /** @var \DOMelement $root */
        $root = $xml->appendChild($xml->createElement('mail'));
        $root->setAttribute('host', Envi::getHost(true));
        Locales::getInstance()->getLocaleXML($root);
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $root->setAttribute($k, $v);
            }
        }
        $this->body = View::render($xml, $template, true);
    }
}
