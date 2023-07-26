<?php

namespace DatabaseBackup;

use Throwable;

class Coroutine
{
    public static function run(callable $fn, ?callable $err = null): void
    {
        try {
            go($fn);
        } catch (Throwable $exception) {
            if ($err) {
                call_user_func($err, $exception);
            }
        }
    }
}