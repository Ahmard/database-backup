# Database Backup

Handles database backup after certain periodic timer

## Installation

```composer require ahmard/database-backup```

## Usage

```php
use DatabaseBackup\Backup;
use DatabaseBackup\Helpers\Console;

// Backup Class (NucleusBackup.php)
class NucleusBackup extends AbstractBackup
{
    protected bool $sendMailOnError = false;
    protected bool $sendMailOnSuccess = false;
    
    public function interval(): int
    {
        return 2_000;
    }

    public function filePath(): string
    {
        return sprintf('%s/nucleus-%s.sql', dirname(__DIR__, 2), uniqid());
    }

    public function onSuccess(string $path, callable $done): void
    {
        $done();
        Console::info('nucleus backup completed');
        unlink($path);
    }


    public function connection(): DatabaseConnection
    {
        return new DatabaseConnection(
            driver: DatabaseDriver::MYSQL,
            host: 'localhost',
            username: 'root',
            password: '1234',
            database: 'nucleus'
        );
    }
}

// Runner (run.php)
use Swoole\Runtime;
use DatabaseBackup\Backup;
use DatabaseBackup\Helpers\Console;

require __DIR__ . '/vendor/autoload.php';

Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

Console::writeln("Backup service started");

// Run backups
Backup::new()->start([NucleusBackup::class]);

```

### Mail Notification

```php

use DatabaseBackup\Backup;

$receivers = [
    new MailReceiver(
        email: 'jane.doe@example.com',
        name: 'Jane Doe'
    ),
];

$smtp = new SmtpCredential(
    host: 'localhost',
    port: 8025,[Helpers](src%2FHelpers)
    username: 'noreply@example.com',
    password: 'Password',
    auth: false
);

Backup::new()
    ->withSmtp($smtp)
    ->withMailReceivers($receivers)
    ->start([NucleusBackup::class]);
```

This library is **MIT Licenced**

Enjoy 😉