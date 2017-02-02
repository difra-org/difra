<?php

namespace Difra\Mailer;

use Drafton\Exception;

/**
 * Class Mail
 * Send mail using mail() function
 * @package Difra\Mailer
 */
class Mail extends Common
{
    /**
     * Send mail
     * @return bool
     * @throws Exception
     */
    public function send()
    {
        $tos = $this->formatTo();
        $headers = $this->getHeaders(true);
        $subject = $this->formatSubject();
        $body = $this->body;
        $success = true;
        foreach ($tos as $to) {
            if (!mail($to, $subject, $body, $headers)) {
                if (sizeof($this->to) == 1) {
                    throw new Exception("Failed to send message.");
                }
                $success = false;
            }
        }
        return $success;
    }
}
