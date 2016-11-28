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
     * Send e-mail message
     * @param string $email To:
     * @param string $subject Subject:
     * @param string $body Message body
     * @param string|bool $fromMail From: (address)
     * @param string|bool $fromName From: (name)
     * @throws Exception
     * @deprecated
     */
    public function sendMail($email, $subject, $body, $fromMail = false, $fromName = false)
    {
        $this->setTo($email);
        if ($fromMail) {
            $this->setFrom([$fromMail, $fromName]);
        }
        $this->setSubject($subject);
        $this->setBody($body);

        $this->send();
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
        $headers = [
            "Mime-Version: 1.0",
            "Content-Type: text/html; charset=\"UTF-8\"",
            "Date: " . date('r'),
            "Message-Id: <" . md5(microtime()) . '-' . md5($from . implode('', $to)) . '@' . Envi::getHost(true) . '>',
            'Content-Transfer-Encoding: 8bit',
            "From: $from"
        ];
        if ($full) {
            foreach ($this->formatTo() as $to) {
                $headers[] = "To: $to";
            }
            $headers[] = "Subject: " . $this->formatSubject();
        }
        return $implode ? implode(self::EOL, $headers) : $headers;
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
        if (preg_match('/[\\x80-\\xff]+/', $subject)) {
            return $this->encodeHeader($subject);
        } else {
            return $subject;
        }
    }

    /**
     * Temporary method from phpmailer
     * // todo: remove
     * @param $str
     * @param string $position
     * @return mixed|string
     * @deprecated
     */
    public function encodeHeader($str, $position = 'text')
    {
        $matchcount = 0;
        switch (strtolower($position)) {
            case 'phrase':
                if (!preg_match('/[\200-\377]/', $str)) {
                    // Can't use addslashes as we don't know the value of magic_quotes_sybase
                    $encoded = addcslashes($str, "\0..\37\177\\\"");
                    if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return ($encoded);
                    } else {
                        return ("\"$encoded\"");
                    }
                }
                $matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $matchcount = preg_match_all('/[()"]/', $str, $matches);
            // Intentional fall-through
            case 'text':
            default:
                $matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                break;
        }
        //There are no chars that need encoding
        if ($matchcount == 0) {
            return ($str);
        }
        $maxlen = 75 - 7 - strlen($this->CharSet);
        // Try to select the encoding which should produce the shortest output
        if ($matchcount > strlen($str) / 3) {
            // More than a third of the content will need encoding, so B encoding will be most efficient
            $encoding = 'B';
            if (function_exists('mb_strlen') && $this->hasMultiBytes($str)) {
                // Use a custom function which correctly encodes and wraps long
                // multibyte strings without breaking lines within a character
                $encoded = $this->base64EncodeWrapMB($str, "\n");
            } else {
                $encoded = base64_encode($str);
                $maxlen -= $maxlen % 4;
                $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
            }
        } else {
            $encoding = 'Q';
            $encoded = $this->encodeQ($str, $position);
            $encoded = $this->wrapText($encoded, $maxlen, true);
            $encoded = str_replace('=' . self::CRLF, "\n", trim($encoded));
        }
        $encoded = preg_replace('/^(.*)$/m', ' =?' . $this->CharSet . "?$encoding?\\1?=", $encoded);
        $encoded = trim(str_replace("\n", $this->LE, $encoded));
        return $encoded;
    }

    /**
     * @param $str
     * @param string $position
     * @return mixed
     */
    public function encodeQ($str, $position = 'text')
    {
        // There should not be any EOL in the string
        $pattern = '';
        $encoded = str_replace(["\r", "\n"], '', $str);
        switch (strtolower($position)) {
            case 'phrase':
                // RFC 2047 section 5.3
                $pattern = '^A-Za-z0-9!*+\/ -';
                break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                // RFC 2047 section 5.2
                $pattern = '\(\)"';
            // intentional fall-through
            // for this reason we build the $pattern without including delimiters and []
            case 'text':
            default:
                // RFC 2047 section 5.1
                // Replace every high ascii, control, =, ? and _ characters
                $pattern = '\000-\011\013\014\016-\037\075\077\137\177-\377' . $pattern;
                break;
        }
        $matches = [];
        if (preg_match_all("/[{$pattern}]/", $encoded, $matches)) {
            // If the string contains an '=', make sure it's the first thing we replace
            // so as to avoid double-encoding
            $eqkey = array_search('=', $matches[0]);
            if (false !== $eqkey) {
                unset($matches[0][$eqkey]);
                array_unshift($matches[0], '=');
            }
            foreach (array_unique($matches[0]) as $char) {
                $encoded = str_replace($char, '=' . sprintf('%02X', ord($char)), $encoded);
            }
        }
        // Replace every spaces to _ (more readable than =20)
        return str_replace(' ', '_', $encoded);
    }

    /**
     * @param $str
     * @return bool
     */
    public function hasMultiBytes($str)
    {
        if (function_exists('mb_strlen')) {
            return (strlen($str) > mb_strlen($str, $this->CharSet));
        } else { // Assume no multibytes (we can't handle without mbstring functions anyway)
            return false;
        }
    }

    /** @var string */
    public $LE = "\n";
    /** @var string */
    public $CharSet = 'utf-8';
    const CRLF = "\r\n";

    /**
     * @param $encodedText
     * @param $maxLength
     * @return mixed
     */
    public function utf8CharBoundary($encodedText, $maxLength)
    {
        $foundSplitPos = false;
        $lookBack = 3;
        while (!$foundSplitPos) {
            $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
            $encodedCharPos = strpos($lastChunk, '=');
            if (false !== $encodedCharPos) {
                // Found start of encoded character byte within $lookBack block.
                // Check the encoded byte value (the 2 chars after the '=')
                $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
                $dec = hexdec($hex);
                if ($dec < 128) {
                    // Single byte character.
                    // If the encoded char was found at pos 0, it will fit
                    // otherwise reduce maxLength to start of the encoded char
                    if ($encodedCharPos > 0) {
                        $maxLength = $maxLength - ($lookBack - $encodedCharPos);
                    }
                    $foundSplitPos = true;
                } elseif ($dec >= 192) {
                    // First byte of a multi byte character
                    // Reduce maxLength to split at start of character
                    $maxLength = $maxLength - ($lookBack - $encodedCharPos);
                    $foundSplitPos = true;
                } elseif ($dec < 192) {
                    // Middle byte of a multi byte character, look further back
                    $lookBack += 3;
                }
            } else {
                // No encoded character found
                $foundSplitPos = true;
            }
        }
        return $maxLength;
    }

    /**
     * @param $message
     * @param $length
     * @param bool $qp_mode
     * @return mixed|string
     */
    public function wrapText($message, $length, $qp_mode = false)
    {
        if ($qp_mode) {
            $soft_break = sprintf(' =%s', $this->LE);
        } else {
            $soft_break = $this->LE;
        }
        // If utf-8 encoding is used, we will need to make sure we don't
        // split multibyte characters when we wrap
        $is_utf8 = (strtolower($this->CharSet) == 'utf-8');
        $lelen = strlen($this->LE);
        $crlflen = strlen(self::CRLF);
        $message = $this->fixEOL($message);
        //Remove a trailing line break
        if (substr($message, -$lelen) == $this->LE) {
            $message = substr($message, 0, -$lelen);
        }
        //Split message into lines
        $lines = explode($this->LE, $message);
        //Message will be rebuilt in here
        $message = '';
        foreach ($lines as $line) {
            $words = explode(' ', $line);
            $buf = '';
            $firstword = true;
            foreach ($words as $word) {
                if ($qp_mode and (strlen($word) > $length)) {
                    $space_left = $length - strlen($buf) - $crlflen;
                    if (!$firstword) {
                        if ($space_left > 20) {
                            $len = $space_left;
                            if ($is_utf8) {
                                $len = $this->utf8CharBoundary($word, $len);
                            } elseif (substr($word, $len - 1, 1) == '=') {
                                $len--;
                            } elseif (substr($word, $len - 2, 1) == '=') {
                                $len -= 2;
                            }
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', self::CRLF);
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while (strlen($word) > 0) {
                        if ($length <= 0) {
                            break;
                        }
                        $len = $length;
                        if ($is_utf8) {
                            $len = $this->utf8CharBoundary($word, $len);
                        } elseif (substr($word, $len - 1, 1) == '=') {
                            $len--;
                        } elseif (substr($word, $len - 2, 1) == '=') {
                            $len -= 2;
                        }
                        $part = substr($word, 0, $len);
                        $word = substr($word, $len);
                        if (strlen($word) > 0) {
                            $message .= $part . sprintf('=%s', self::CRLF);
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    if (!$firstword) {
                        $buf .= ' ';
                    }
                    $buf .= $word;
                    if (strlen($buf) > $length and $buf_o != '') {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
                $firstword = false;
            }
            $message .= $buf . self::CRLF;
        }
        return $message;
    }

    /**
     * @param $str
     * @return mixed
     */
    public function fixEOL($str)
    {
        // Normalise to \n
        $nstr = str_replace(["\r\n", "\r"], "\n", $str);
        // Now convert LE as needed
        if ($this->LE !== "\n") {
            $nstr = str_replace("\n", $this->LE, $nstr);
        }
        return $nstr;
    }

    /**
     * @param $str
     * @param null $linebreak
     * @return string
     */
    public function base64EncodeWrapMB($str, $linebreak = null)
    {
        $start = '=?' . $this->CharSet . '?B?';
        $end = '?=';
        $encoded = '';
        if ($linebreak === null) {
            $linebreak = $this->LE;
        }
        $mb_length = mb_strlen($str, $this->CharSet);
        // Each line must have length <= 75, including $start and $end
        $length = 75 - strlen($start) - strlen($end);
        // Average multi-byte ratio
        $ratio = $mb_length / strlen($str);
        // Base64 has a 4:3 ratio
        $avgLength = floor($length * $ratio * .75);
        for ($i = 0; $i < $mb_length; $i += $offset) {
            $lookBack = 0;
            do {
                $offset = $avgLength - $lookBack;
                $chunk = mb_substr($str, $i, $offset, $this->CharSet);
                $chunk = base64_encode($chunk);
                $lookBack++;
            } while (strlen($chunk) > $length);
            $encoded .= $chunk . $linebreak;
        }
        // Chomp the last linefeed
        $encoded = substr($encoded, 0, -strlen($linebreak));
        return $encoded;
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
