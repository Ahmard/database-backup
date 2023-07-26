<?php

namespace DatabaseBackup\Services;

use DatabaseBackup\MailReceiver;
use DatabaseBackup\SmtpCredential;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * @phpstan-consistent-constructor
 */
class MailService
{
    protected array $params = [
        'body' => '',
        'subject' => '',
        'alt_body' => '',
        'reply_to' => [],
        'receivers' => [],
        'attachments' => [],
        'debug' => false,
    ];


    /**
     * @param SmtpCredential $credential
     * @return static
     */
    public static function new(SmtpCredential $credential): static
    {
        return new static($credential);
    }

    public function __construct(
        private readonly SmtpCredential $smtpCredential,
    )
    {
    }

    public function withDebug(bool $status = true): static
    {
        $this->params['debug'] = $status;
        return $this;
    }

    /**
     * @param string $body string/html message of the email
     * @return static
     */
    public function setBody(string $body): static
    {
        $this->params['body'] = $body;
        return $this;
    }

    /**
     * @param string $subject
     * @return static
     */
    public function setSubject(string $subject): static
    {
        $this->params['subject'] = $subject;
        return $this;
    }

    /**
     * @param string $body string alt message of the email
     * @return static
     */
    public function setAltBody(string $body): static
    {
        $this->params['alt_body'] = $body;
        return $this;
    }

    /**
     * @param MailReceiver[] $emails
     * @return $this
     */
    public function setReceivers(array $emails): static
    {
        $this->params['receivers'] = array_merge($this->params['receivers'], $emails);
        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function send(): void
    {
        $mail = new PHPMailer(true); // Server settings

        // Debug settings
        $mail->SMTPDebug = $this->params['debug'];

        $mail->isSMTP();
        $mail->Host = $this->smtpCredential->host;
        $mail->Port = $this->smtpCredential->port;
        $mail->SMTPSecure = $this->smtpCredential->encryption ?? '';
        $mail->SMTPAuth = $this->smtpCredential->auth;
        $mail->Username = $this->smtpCredential->username;
        $mail->Password = $this->smtpCredential->password;

        // Sender
        $mail->setFrom(
            $this->smtpCredential->fromEmail ?? $this->smtpCredential->username,
            $this->smtpCredential->fromName ?? ''
        );

        /**
         * Recipients
         * @var MailReceiver $receiver
         */
        foreach ($this->params['receivers'] as $receiver) {
            $mail->addAddress($receiver->email, $receiver->name);
        }

        // Content
        $mail->isHTML();
        $mail->Subject = $this->params['subject'];
        $mail->Body = $this->params['body'];

        if (array_key_exists('alt_message', $this->params)) {
            $mail->AltBody = $this->params['alt_message'];
        }

        $mail->send();
    }
}
