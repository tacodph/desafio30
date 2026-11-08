<?php

namespace App\Mail;

use Illuminate\Mail\MailManager;

class AuditedMailManager extends MailManager
{
    public function build($config)
    {
        $mailer = new AuditedMailer($config['name'] ?? 'ondemand', $this->app['view'], $this->createSymfonyTransport($config), $this->app['events']);
        if ($this->app->bound('queue')) {
            $mailer->setQueue($this->app['queue']);
        }

        return $mailer;
    }
}
