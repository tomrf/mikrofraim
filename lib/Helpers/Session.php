<?php

namespace Mikrofraim\Helpers;

class Session
{
    public function __construct(?string $name = null)
    {
        if ($this->sessionDisabled()) {
            return;
        }
        if ($name !== null) {
            session_name($name);
        }
        session_start();
    }

    /**
     * Set a session key
     * @param  string $key
     * @param  mixed $value
     * @return mixed return set value
     */
    public function set(string $key, $value)
    {
        return $_SESSION[$key] = $value;
    }

    /**
     * Get a session key value
     * @param  string $key
     * @return null|mixed
     */
    public function get(string $key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }

    /**
     * Delete a session key
     * @param  string $key
     * @return boolean
     */
    public function delete(string $key): bool
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }

    /**
     * Return value of MIKROFRAIM_TESTSUITE
     * @return boolean
     */
    protected function sessionDisabled(): bool
    {
        return defined('MIKROFRAIM_TESTSUITE');
    }

    /**
     * Clear the session
     * @return boolean
     */
    public function clear(): bool
    {
        if ($this->sessionDisabled()) {
            return true;
        }
        session_destroy();
        return true;
    }
}
