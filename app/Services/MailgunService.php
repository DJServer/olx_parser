<?php

namespace App\Services;

use Mailgun\Mailgun;

class MailgunService
{
    protected $mg;
    protected $domain;
    protected $fromEmail;

    public function __construct()
    {
        $this->domain = env('MAILGUN_DOMAIN');
        $this->mg = Mailgun::create(env('MAILGUN_SECRET'), env('MAILGUN_ENDPOINT').$this->domain);
        $this->fromEmail = env('MAIL_FROM_ADDRESS', 'no-reply@tourgid.online');
    }

    public function sendEmail($to, $subject, $message)
    {
        return $this->mg->messages()->send($this->domain, [
            'from'    => $this->fromEmail,
            'to'      => $to,
            'subject' => $subject,
            'text'    => $message
        ]);
    }
}
