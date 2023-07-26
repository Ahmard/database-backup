<?php

namespace DatabaseBackup\Services;

use DatabaseBackup\AbstractBackup;
use DatabaseBackup\DatabaseConnection;
use DatabaseBackup\DatabaseDriver;
use DatabaseBackup\Helpers\Console;
use DatabaseBackup\MailReceiver;
use DatabaseBackup\NoSmtp;
use DatabaseBackup\SmtpCredential;
use InvalidArgumentException;
use PHPMailer\PHPMailer\Exception;
use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Throwable;

class BackupService
{
    /**
     * @param AbstractBackup $backup
     * @param SmtpCredential $smtpCredential
     * @param MailReceiver[] $mailReceivers
     * @return void
     * @throws Exception
     */
    public function takeBackup(
        AbstractBackup $backup,
        SmtpCredential $smtpCredential,
        array          $mailReceivers
    ): void
    {
        $connection = $backup->connection();

        try {
            Console::comment(sprintf('running: %s', $backup::class));

            match ($connection->driver) {
                DatabaseDriver::MYSQL => $this->takeMysqlBackup($connection, $backup->getBackupFilePath()),
                DatabaseDriver::POSTGRES => $this->takePostgresBackup($connection, $backup->getBackupFilePath()),
            };

            if ($backup->willSendMailOnSuccess()) {
                go(fn() => $this->makeMailService($smtpCredential, $backup->smtpCredential())
                    ->setReceivers($mailReceivers)
                    ->setSubject(sprintf('[%s] Backup Success', $backup::class))
                    ->setBody('Backup taking succeeded')
                    ->send());
            }

            $backup->onSuccess($backup->getBackupFilePath(), function () use ($backup) {
                Console::lightGreen(sprintf('finished: %s', $backup::class));
            });

        } catch (Throwable $exception) {
            Console::error('failure while taking backup');

            if ($backup->willSendMailOnError()) {
                go(function () use ($smtpCredential, $backup, $mailReceivers,) {
                    $this->makeMailService($smtpCredential, $backup->smtpCredential())
                        ->setReceivers($mailReceivers)
                        ->setSubject(sprintf('[%s] Backup Failure', $backup::class))
                        ->setBody(sprintf('Backup service "%s" failed', $backup::class))
                        ->send();

                    $backup->onAfter($backup->getBackupFilePath());
                });
            }

            $backup->onError($exception);
        }
    }

    protected function makeMailService(SmtpCredential $smtpCredential, SmtpCredential $backupSmtpCredential): MailService
    {
        $credential = $backupSmtpCredential;
        if ($credential instanceof NoSmtp) {
            $credential = $smtpCredential;
        }

        if ($credential instanceof NoSmtp) {
            throw new InvalidArgumentException('Please provide smtp credentials');
        }

        return MailService::new($credential);
    }

    protected function takeMysqlBackup(DatabaseConnection $connection, string $path): void
    {
        MySql::create()
            ->setDbName($connection->database)
            ->setUserName($connection->username)
            ->setPassword($connection->password)
            ->setHost($connection->host)
            ->dumpToFile($path);
    }

    protected function takePostgresBackup(DatabaseConnection $connection, string $path): void
    {
        PostgreSql::create()
            ->setDbName($connection->database)
            ->setUserName($connection->username)
            ->setPassword($connection->password)
            ->setHost($connection->host)
            ->dumpToFile($path);
    }
}