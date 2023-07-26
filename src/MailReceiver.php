<?php

namespace DatabaseBackup;

readonly class MailReceiver
{
    public function __construct(
        public string $email,
        public string $name,
    )
    {
    }
}