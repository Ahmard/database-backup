<?php

namespace DatabaseBackup;

readonly class DatabaseConnection
{
    public function __construct(
        public DatabaseDriver $driver,
        public string         $host,
        public string         $username,
        public string         $password,
        public string         $database,
        public ?int           $port = null,
    )
    {
    }
}