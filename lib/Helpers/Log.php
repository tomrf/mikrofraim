<?php

namespace Mikrofraim;

class Log
{
    private static $logger = null;

    public static function init()
    {
        $formatter = new \Monolog\Formatter\LineFormatter("%datetime% > %level_name% > %message%\n");
        $stream = new \Monolog\Handler\StreamHandler('../' . getenv('LOG_FILENAME'), \Monolog\Logger::DEBUG);
        $stream->setFormatter($formatter);
        self::$logger = new \Monolog\Logger('_');
        self::$logger->pushHandler($stream);
    }

    public static function debug($message)
    {
        if (self::$logger) {
            self::$logger->debug($message);
        }
    }

    public static function info($message)
    {
        if (self::$logger) {
            self::$logger->info($message);
        }
    }

    public static function notice($message)
    {
        if (self::$logger) {
            self::$logger->notice($message);
        }
    }

    public static function warning($message)
    {
        if (self::$logger) {
            self::$logger->warning($message);
        }
    }

    public static function error($message)
    {
        if (self::$logger) {
            self::$logger->error($message);
        }
    }

}
