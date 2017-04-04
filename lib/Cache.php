<?php

class Cache
{
    private static $cache = null;
    private static $fileCachePath = '../storage/cache/filecache';

    private static function writeCache()
    {
        return file_put_contents(self::$fileCachePath, json_encode(self::$cache));
    }

    private static function readCache()
    {
        self::$cache = (array) json_decode(file_get_contents(self::$fileCachePath), true);
        if (! self::$cache) {
            return false;
        }
        return true;
    }

    public static function isFileCachePathWritable()
    {
        if (! file_exists(self::$fileCachePath)) {
            self::writeCache();
        }
        return is_writable(self::$fileCachePath);
    }

    public static function set($key, $value, $ttl = null)
    {
        if (! self::$cache) {
            self::readCache();
        }
        self::$cache[$key]['value'] = $value;
        if ($ttl) {
            self::$cache[$key]['expiration'] = (time() + $ttl);
        }
        if (! self::writeCache()) {
            return false;
        }
        return true;
    }

    public static function get($key)
    {
        if (! self::$cache) {
            self::readCache();
        }
        if (isset(self::$cache[$key])) {
            if (isset(self::$cache[$key]['expiration'])) {
                if (self::$cache[$key]['expiration'] < time()) {
                    self::forget($key);
                    return null;
                }
            }
            return self::$cache[$key]['value'];
        }
        return null;
    }

    public static function forget($key)
    {
        if (! self::$cache) {
            self::readCache();
        }
        $ret = false;
        if (isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
            $ret = true;
        }
        if (! self::writeCache()) {
            $ret = false;
        }
        return $ret;
    }

    public static function clear()
    {
        self::$cache = null;
        self::writeCache();
    }

}
