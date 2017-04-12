<?php

namespace Mikrofraim\Cache;

class InvalidArgumentException extends \Exception implements \Psr\SimpleCache\InvalidArgumentException
{
}

class FileCache implements \Psr\SimpleCache\CacheInterface
{
    private $cache = null;
    private $fileCachePath = PROJECT_DIRECTORY . '/storage/cache/filecache';
    private $cacheRead = false;

    public function init()
    {
        register_shutdown_function(function() {
            if ($this->cacheRead) {
                $this->writeCache();
            }
        });
    }

    private function writeCache()
    {
        return file_put_contents($this->fileCachePath, json_encode($this->cache));
    }

    private function readCache()
    {
        $this->cacheRead = true;
        $this->cache = (array) json_decode(file_get_contents($this->fileCachePath), true);
        if ($this->cache === null) {
            return false;
        }
        return true;
    }

    private function isKeyValid($key)
    {
        $validCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789._-';

        if (! is_string($key)) {
            return false;
        }

        $keyLength = strlen($key);

        if (! $keyLength) {
            return false;
        }

        for ($i = 0; $i < $keyLength; $i++) {
            if (strpos($validCharacters, $key[$i]) === false) {
                return false;
            }
        }

        return true;
    }

    public function isFileCachePathWritable()
    {
        if (! file_exists($this->fileCachePath)) {
            if (! touch($this->fileCachePath)) {
                return false;
            }
            $this->writeCache();
        }

        return is_writable($this->fileCachePath);
    }

    public function set($key, $value, $ttl = null)
    {
        if (is_integer($key)) {
            $key = strval($key);
        }

        if (! $this->isKeyValid($key)) {
            throw new InvalidArgumentException;
        }

        if ($ttl !== null && ! is_integer($ttl)) {
            if (is_object($ttl)) {
                if (get_class($ttl) !== 'DateInterval') {
                    throw new InvalidArgumentException;
                }
            } else {
                throw new InvalidArgumentException;
            }
        }

        if ($this->cache === null) {
            $this->readCache();
        }

        $this->cache[$key]['v'] = serialize($value);

        if ($ttl !== null) {
            if (is_integer($ttl)) {
                $ttl = $ttl;
            }
            else if (get_class($ttl) === 'DateInterval') {
                $date = new \DateTime();
                $date->add($ttl);
                $ttl = $date->getTimestamp() - time();
            }

            if ($ttl > 0) {
                $this->cache[$key]['e'] = (time() + $ttl);
            } else {
                $this->delete($key);
            }
        }

        return true;
    }

    public function get($key, $default = null)
    {
        if (! $this->isKeyValid($key)) {
            throw new InvalidArgumentException;
        }

        if ($this->cache === null) {
            $this->readCache();
        }

        if (isset($this->cache[$key])) {
            if (isset($this->cache[$key]['e'])) {
                if ($this->cache[$key]['e'] < time()) {
                    $this->delete($key);
                    return $default;
                }
            }
            return unserialize($this->cache[$key]['v']);
        }

        return $default;
    }

    public function delete($key)
    {
        if (! $this->isKeyValid($key)) {
            throw new InvalidArgumentException;
        }

        if ($this->cache === null) {
            $this->readCache();
        }

        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
        }

        return true;
    }

    public function clear()
    {
        $this->cache = null;
        $this->writeCache();

        return true;
    }

    public function setMultiple($values, $ttl = null)
    {
        if (! is_array($values) && ! $values instanceof Traversable && ! is_a($values, 'Generator')) {
            throw new InvalidArgumentException;
        }

        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function getMultiple($keys, $default = null)
    {
        if (! is_array($keys) && ! $keys instanceof Traversable && ! is_a($keys, 'Generator')) {
            throw new InvalidArgumentException;
        }

        $ret = [];

        foreach ($keys as $key) {
            $cacheValue = $this->get($key, $default);
            $ret[$key] = $cacheValue;
        }

        return $ret;
    }

    public function deleteMultiple($keys)
    {
        if (! is_array($keys) && ! $keys instanceof Traversable && ! is_a($keys, 'Generator')) {
            throw new InvalidArgumentException;
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has($key)
    {
        if ($this->get($key) !== null) {
            return true;
        }
        return false;
    }

}
