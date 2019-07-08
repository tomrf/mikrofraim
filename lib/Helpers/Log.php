<?php

namespace Mikrofraim\Helpers;

class Log
{
    /**
     * Monolog Logger object
     * @var mixed
     */
    private $logger = null;

    public function __construct(string $filename, string $format)
    {
        $formatter = new \Monolog\Formatter\LineFormatter($format);
        $stream = new \Monolog\Handler\StreamHandler($filename, \Monolog\Logger::DEBUG);
        $stream->setFormatter($formatter);
        $this->logger = new \Monolog\Logger('_');
        $this->logger->pushHandler($stream);
    }

    /**
     * log shortcut for debug()
     * @param  string $message
     */
    public function debug(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->debug($message);
        }
    }

    /**
     * log shortcut for info()
     * @param  string $message
     */
    public function info(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->info($message);
        }
    }

    /**
     * log shortcut for notice()
     * @param  string $message
     */
    public function notice(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->notice($message);
        }
    }

    /**
     * log shortcut for warning()
     * @param  string $message
     */
    public function warning(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->warning($message);
        }
    }

    /**
     * log shortcut for error()
     * @param  string $message
     */
    public function error(string $message): void
    {
        if ($this->logger !== null) {
            $this->logger->error($message);
        }
    }
}
