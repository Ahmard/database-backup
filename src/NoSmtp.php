<?php

namespace DatabaseBackup;

readonly class NoSmtp extends SmtpCredential
{
    public static function new(): NoSmtp
    {
        return new NoSmtp(
            host: '',
            port: 0,
            username: '',
            password: '',
            fromEmail: '',
            fromName: ''
        );
    }
}