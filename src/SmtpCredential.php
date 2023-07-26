<?php

namespace DatabaseBackup;

readonly class SmtpCredential
{
    public function __construct(
        public string  $host,
        public int     $port,
        public string  $username,
        public string  $password,
        public ?string $fromEmail = null,
        public ?string $fromName = null,
        public bool    $auth = true,
        public ?string $encryption = null,
    )
    {
    }
}