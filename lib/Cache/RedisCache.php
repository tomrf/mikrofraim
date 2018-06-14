<?php

namespace Mikrofraim\Cache;

class RedisCache extends ArrayCache implements \Psr\SimpleCache\CacheInterface
{
    protected $redisConnection = null;

    public function init()
    {
        $this->redisConnection = new \Redis();

        $redisHost = getenv('CACHE_REDIS_HOSTNAME') ? getenv('CACHE_REDIS_HOSTNAME') : '127.0.0.1';
        $redisPort = getenv('CACHE_REDIS_PORT') ? getenv('CACHE_REDIS_PORT') : 6379;

        if (! $this->redisConnection->connect($redisHost, $redisPort)) {
            throw new \Exception('Redis connection failed');
        }

        if (getenv('CACHE_REDIS_PASSWORD')) {
            if (! $this->redisConnection->auth(getenv('CACHE_REDIS_PASSWORD'))) {
                throw new \Exception('Redis authentication failed');
            }
        }
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
                } else {
                    $ttl = $date->getTimestamp() - time();
                }
            } else {
                throw new InvalidArgumentException;
            }
        }

        $this->redisConnection->set($key, serialize($value), $ttl);

        return true;

    }

    public function get($key, $default = null)
    {
        if (! $this->isKeyValid($key)) {
            throw new InvalidArgumentException;
        }
        $value = $this->redisConnection->get($key);
        if ($value) {
            return unserialize($value);
        } else {
            return $default;
        }
    }

    public function delete($key)
    {
        if (! $this->isKeyValid($key)) {
            throw new InvalidArgumentException;
        }
        $this->redisConnection->delete($key);
        return true;
    }

    public function clear()
    {
        $this->redisConnection->flushAll();
    }

}
