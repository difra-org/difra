<?php

namespace Difra;

/**
 * Class Mailer
 * @package Difra
 */
class Mailer
{
    private $fromText = 'Robot';
    private $fromMail = 'robot@example.com'; // TODO: move this to configuration and throw exception if it's not set

    /**
     * Singleton
     * @return self
     */
    public static function getInstance()
    {
        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * Contructor
     */
    public function __construct()
    {
        $this->fromMail = 'robot@' . Envi::getHost(true);
    }

    /**
     * Send e-mail message
     * @param string $email To:
     * @param string $subject Subject:
     * @param string $body Message body
     * @param string|bool $fromMail From: (address)
     * @param string|bool $fromText From: (name)
     * @return bool
     * @throws exception
     */
    public function sendMail($email, $subject, $body, $fromMail = false, $fromText = false)
    {
        if (!$fromMail) {
            $fromMail = $this->fromMail;
        }
        if (!$fromText) {
            $fromText = $this->fromText;
        }

        $headers = [
            "Mime-Version: 1.0",
            "Content-Type: text/html; charset=\"UTF-8\"",
            "Date: " . date('r'),
            "Message-Id: <" . md5(microtime()) . '-' . md5($fromMail . $email) . '@' . Envi::getHost(true) . '>',
            'Content-Transfer-Encoding: 8bit'
        ];

        // Encode non-ascii text strings to base64
        if (preg_match('/[\\x80-\\xff]+/', $fromText)) {
            $headers[] = 'From: =?utf-8?B?' . base64_encode($fromText) . "==?= <{$fromMail}>";
        } else {
            $headers[] = "From: $fromText <$fromMail>";
        }
        if (preg_match('/[\\x80-\\xff]+/', $subject)) {
            $subj = '=?utf-8?B?' . base64_encode($subject) . '==?=';
        } else {
            $subj = $subject;
        }

        if (!mail($email, $subj, $body, implode("\r\n", $headers))) {
            throw new Exception("Failed to send message to $email.");
        }
        return true;
    }

    /**
     * Generate and send e-mail message
     * Data are passed to templates as <mail> node attributes.
     * Message template can contain following tags: from, fromtext, subject, text
     * @param string $email
     * @param string $template
     * @param array $data
     */
    public function createMail($email, $template, $data)
    {
        $xml = new \DOMDocument();
        $root = $xml->appendChild($xml->createElement('mail'));
        $this->addData($root, $data);
        $view = new View;
        $templateText = $view->render($xml, $template, true);

        preg_match('|<subject[^>]*>(.*)</subject>|Uis', $templateText, $subject);
        preg_match('|<text[^>]*>(.*)</text>|Uis', $templateText, $mailText);
        preg_match('|<from[^>]*>(.*)</from>|Uis', $templateText, $fromMail);
        preg_match('|<fromtext[^>]*>(.*)</fromtext>|Uis', $templateText, $fromText);
        $subject = !empty($subject[1]) ? $subject[1] : '';
        $mailText = !empty($mailText[1]) ? $mailText[1] : '';
        $fromMail = !empty($fromMail[1]) ? $fromMail[1] : $this->fromMail;
        $fromText = !empty($fromText[1]) ? $fromText[1] : $this->fromText;

        $this->sendMail($email, $subject, $mailText, $fromMail, $fromText);
    }

    /**
     * Add some data to output XML for message template
     * @param \DOMElement|\DOMNode $node
     * @param array $data
     */
    private function addData($node, $data)
    {
        $node->setAttribute('host', Envi::getHost(true));
        Locales::getInstance()->getLocaleXML($node);
        foreach ($data as $k => $v) {
            $node->setAttribute($k, $v);
        }
    }
}
