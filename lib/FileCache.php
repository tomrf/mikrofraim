<?php

class FileCache
{
    private static $cache = null;
    private static $fileCachePath = PROJECT_DIRECTORY . '/storage/cache/filecache';

    public static function init()
    {
        register_shutdown_function(function() {
            self::writeCache();
        });
    }

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
            if (! touch(self::$fileCachePath)) {
                return false;
            }
            self::writeCache();
        }
        return is_writable(self::$fileCachePath);
    }

    public static function set($key, $value, $ttl = null)
    {
        if (! self::$cache) {
            self::readCache();
        }
        self::$cache[$key]['v'] = $value;
        if ($ttl) {
            self::$cache[$key]['e'] = (time() + $ttl);
        }
        return self::$cache[$key]['v'];
    }

    public static function get($key)
    {
        if (! self::$cache) {
            self::readCache();
        }
        if (isset(self::$cache[$key])) {
            if (isset(self::$cache[$key]['e'])) {
                if (self::$cache[$key]['e'] < time()) {
                    self::forget($key);
                    return null;
                }
            }
            return self::$cache[$key]['v'];
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
        return $ret;
    }

    public static function clear()
    {
        self::$cache = null;
        self::writeCache();
    }

}
