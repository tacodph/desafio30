<?php

namespace App\Mail;

use App\Models\EmailLog;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Mail\Transport\LogTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

class AuditedMailer extends Mailer
{
    protected function shouldSendMessage($message, $data = [])
    {
        if (isset($data['__laravel_notification'])) {
            $message->getHeaders()->addTextHeader('X-Desafio-Notification', $data['__laravel_notification']);
        }

        return parent::shouldSendMessage($message, $data);
    }

    protected function sendSymfonyMessage(Email $message)
    {
        $headers = $message->getHeaders();
        $existingId = $headers->get('X-Desafio-Email-Log')?->getBodyAsString();
        $category = $headers->get('X-Desafio-Category')?->getBodyAsString() ?? 'platform';
        $notification = $headers->get('X-Desafio-Notification')?->getBodyAsString();
        $sensitive = $notification !== null || preg_match('~/(reset-password|email/verify)(/|\?)~', ($message->getTextBody() ?? '').($message->getHtmlBody() ?? ''));
        $addresses = fn (array $list) => array_map(fn (Address $address) => ['email' => $address->getAddress(), 'name' => $address->getName()], $list);
        $recipients = ['to' => $addresses($message->getTo()), 'cc' => $addresses($message->getCc()), 'bcc' => $addresses($message->getBcc())];
        $plain = $message->getTextBody() ?? html_entity_decode(strip_tags(str_replace(['</p>', '<br>', '<br/>', '<br />', '</div>'], "\n", $message->getHtmlBody() ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $log = $existingId ? EmailLog::findOrFail($existingId) : new EmailLog;
        $log->fill([
            'actor_id' => $log->actor_id ?? (app()->runningInConsole() ? null : request()->user()?->id),
            'category' => $notification ? 'notification' : $category,
            'recipient_email' => ($message->getTo()[0] ?? $message->getCc()[0] ?? $message->getBcc()[0] ?? null)?->getAddress() ?? '',
            'recipients' => $recipients, 'sender' => $addresses($message->getFrom()), 'reply_to' => $addresses($message->getReplyTo()),
            'subject' => $message->getSubject() ?? '',
            'body_text' => $sensitive ? 'Conteúdo de autenticação omitido para não armazenar links e tokens de acesso.' : $plain,
            'body_html' => $sensitive ? null : $message->getHtmlBody(),
            'mailer' => $this->name, 'status' => 'sending', 'error' => null,
            'attempts' => ($log->attempts ?? 0) + 1, 'attempted_at' => now(),
        ])->save();
        foreach (['X-Desafio-Email-Log', 'X-Desafio-Category', 'X-Desafio-Notification'] as $header) {
            $headers->remove($header);
        }

        try {
            $sent = parent::sendSymfonyMessage($message);
        } catch (Throwable $exception) {
            $log->update(['status' => 'failed', 'error' => $this->safeError($exception)]);
            throw $exception;
        }

        $local = $this->transport instanceof LogTransport || $this->transport instanceof ArrayTransport;
        $aggregate = $this->transport instanceof \Symfony\Component\Mailer\Transport\RoundRobinTransport;
        $status = ! $sent ? 'failed' : ($local ? 'logged' : ($aggregate ? 'processed' : 'sent'));
        $log->update([
            'status' => $status, 'sent_at' => $status === 'sent' ? now() : null,
            'message_id' => $sent?->getMessageId(),
            'error' => ! $sent ? 'O transporte não confirmou o processamento da mensagem.' : null,
        ]);

        if (! $sent) {
            throw new \RuntimeException('O transporte não confirmou o processamento da mensagem.');
        }

        return $sent;
    }

    private function safeError(Throwable $exception): string
    {
        $error = $exception->getMessage();
        foreach (config('mail.mailers', []) as $config) {
            foreach (['password', 'url'] as $key) {
                if (is_string($config[$key] ?? null) && $config[$key] !== '') {
                    $error = str_replace($config[$key], '[omitido]', $error);
                }
            }
        }

        return mb_substr($error, 0, 3000);
    }
}
