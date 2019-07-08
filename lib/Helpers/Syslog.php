<?php

namespace Mikrofraim\Helpers;

class Syslog
{
    /**
     * Syslog identity string
     * @var string
     */
    private $ident;

    public function __construct(string $ident = 'php')
    {
        $this->ident = $ident;
    }

    /**
     * Send log message to syslog
     * @param string $message
     * @param mixed  $level
     */
    public function log(string $message, $level = LOG_INFO): void
    {
        $stringToLevel = [
            'debug' => LOG_DEBUG,
            'notice' => LOG_NOTICE,
            'warn' => LOG_WARNING,
            'error' => LOG_ERR,
            'err' => LOG_ERR,
            'crit' => LOG_CRIT,
            'alert' => LOG_ALERT,
            'emerg' => LOG_EMERG,
        ];

        if (!is_numeric($level)) {
            if (isset($stringToLevel[$level])) {
                $level = $stringToLevel[$level];
            } else {
                throw new \Exception('Unknown syslog level: ' . $level);
            }
        }

        if (!is_integer($level)) {
            throw new \Exception('Unknown syslog level: ' . $level);
        }

        openlog($this->ident, LOG_NDELAY | LOG_PID, LOG_USER);
        syslog($level, $message);
        closelog();
    }
}
