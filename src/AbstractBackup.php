<?php

namespace DatabaseBackup;

use DatabaseBackup\Helpers\Console;
use Throwable;

abstract class AbstractBackup
{
    protected bool $sendMailOnError = true;
    protected bool $sendMailOnSuccess = false;
    private string $backupFilePath;


    abstract public function connection(): DatabaseConnection;

    abstract public function interval(): int;

    abstract protected function filePath(): string;

    abstract public function onSuccess(string $path, callable $done): void;


    public function onBefore(string $path): void
    {

    }

    public function onAfter(string $path): void
    {

    }

    public function onError(Throwable $exception): void
    {
        $backupNs = $this::class;
        Console::error("[$backupNs]: $exception");
    }

    public function smtpCredential(): SmtpCredential
    {
        return NoSmtp::new();
    }

    public function willSendMailOnError(): bool
    {
        return $this->sendMailOnError;
    }

    public function willSendMailOnSuccess(): bool
    {
        return $this->sendMailOnSuccess;
    }

    /**
     * @return string
     */
    public function getBackupFilePath(): string
    {
        if (!isset($this->backupFilePath)) {
            $this->backupFilePath = $this->filePath();
        }

        return $this->backupFilePath;
    }
}