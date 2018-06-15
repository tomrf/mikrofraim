<?php

namespace Mikrofraim;

class Syslog
{
    public static function log($message, $level = LOG_INFO)
    {
        $stringToLevel = [
            'debug'  => LOG_DEBUG,
            'notice' => LOG_NOTICE,
            'warn'   => LOG_WARNING,
            'error'  => LOG_ERR,
            'err'    => LOG_ERR,
            'crit'   => LOG_CRIT,
            'alert'  => LOG_ALERT,
            'emerg'  => LOG_EMERG,
        ];

        if (! is_numeric($level)) {
            if (isset($stringToLevel[$level])) {
                $level = $stringToLevel[$level];
            } else {
                throw new \Exception('Unknown syslog level: ' . $level);
            }
        }

        $ident = getenv('SYSLOG_IDENT') ? getenv('SYSLOG_IDENT') : null;
        openlog($ident, LOG_NDELAY | LOG_PID, LOG_USER);
        syslog($level, $message);
        closelog();
    }
}
