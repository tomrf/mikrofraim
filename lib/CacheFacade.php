<?php

namespace Mikrofraim\Cache;

class CacheFacade
{
    private static $cache = null;

    public static function init($cache)
    {
        self::$cache = $cache;
    }

    public static function set($key, $value, $ttl = null)
    {
        return self::$cache->set($key, $value, $ttl);
    }

    public static function get($key, $default = null)
    {
        return self::$cache->get($key, $default);
    }

    public static function delete($key)
    {
        return self::$cache->delete($key);
    }

    public static function clear()
    {
        return self::$cache->clear();
    }

    public static function setMultiple($values, $ttl = null)
    {
        return self::$cache->setMultiple($values, $ttl);
    }

    public static function getMultiple($keys, $default = null)
    {
        return self::$cache->getMultiple($keys, $default);
    }

    public static function deleteMultiple($keys)
    {
        return self::$cache->deleteMultiple($keys);
    }

    public static function has($key)
    {
        return self::$cache->has($key);
    }

}
