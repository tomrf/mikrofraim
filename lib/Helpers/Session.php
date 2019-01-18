<?php

namespace Mikrofraim;

class Session
{
    private static $started = false;

    private static function start()
    {
        if (self::sessionDisabled()) {
            return;
        }
        if (!self::$started) {
            if (getenv('SESSION_NAME')) {
                session_name(getenv('SESSION_NAME'));
            }
            session_start();
            self::$started = true;
        }
    }

    public static function set($key, $value)
    {
        self::start();
        return $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    public static function delete($key)
    {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    protected static function sessionDisabled() : bool
    {
        return defined('MIKROFRAIM_TESTSUITE');
    }

    public static function clear()
    {
        if (self::sessionDisabled()) {
            return;
        }
        self::start();
        session_destroy();
        return true;
    }

}
