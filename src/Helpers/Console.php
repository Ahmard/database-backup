<?php

namespace DatabaseBackup\Helpers;

class Console
{
    private static string $baseDirectory;

    public static function getPrefix(): string
    {
        $stackTrace = debug_backtrace();

        $lastSegment = end($stackTrace);

        if (!isset(self::$baseDirectory)) {
            /**@phpstan-ignore-next-line* */
            $fileName = is_array($lastSegment) ? $lastSegment['file'] : __FILE__;
            $exp = explode(DIRECTORY_SEPARATOR, $fileName);
            unset($exp[count($exp) - 1]);
            self::$baseDirectory = implode(DIRECTORY_SEPARATOR, $exp) . '/';
        }

        /**@phpstan-ignore-next-line* */
        $namespace = $lastSegment['file'] ?? $stackTrace[2]['file'];
        $namespace = explode(self::$baseDirectory, $namespace)[1];
        $expNs = explode('src' . DIRECTORY_SEPARATOR, $namespace);
        $namespace = count($expNs) > 1 ? $expNs[1] : $expNs[0];
        return sprintf('[%s] ', $namespace);
    }

    public static function comment(string $message): void
    {
        self::writeWithTimestamp(self::withColor('0;33', $message));
    }

    public static function info(string $message): void
    {
        self::writeWithTimestamp(self::withColor('0;32', $message));
    }

    public static function question(string $message): void
    {
        self::writeWithTimestamp(self::withColor('0;36', $message));
    }

    public static function error(string $message): void
    {
        self::writeWithTimestamp(self::withColor('0;31', $message));
    }

    public static function lightRed(string $message): void
    {
        self::writeWithTimestamp(self::withColor('1;31', $message));
    }

    public static function lightGreen(string $message): void
    {
        self::writeWithTimestamp(self::withColor('1;32', $message));
    }

    public static function lightCyan(string $message): void
    {
        self::writeWithTimestamp(self::withColor('1;36', $message));
    }

    public static function echo(string $message): void
    {
        self::writeWithTimestamp($message);
    }

    public static function write(string $message): void
    {
        self::writeWithTimestamp($message, false);
    }

    public static function writeln(string $message): void
    {
        self::writeWithTimestamp($message);
    }

    private static function writeWithTimestamp(string $message, bool $newLine = true): void
    {
        $message = self::prependTime(self::getPrefix() . $message);
        self::writeWithoutTimestamp($message, $newLine, false);
    }

    private static function writeWithoutTimestamp(string $message, bool $newLine = true, bool $prefix = true): void
    {
        $message = $prefix ? self::getPrefix() . $message : $message;
        echo $message . ($newLine ? PHP_EOL : null);
    }

    protected static function withColor(string $code, string $message): string
    {
        return sprintf("\033[%sm%s\033[0m", $code, $message);
    }

    protected static function prependTime(string $message): string
    {
        return date('[Y-m-d H:i:s]') . " $message";
    }
}