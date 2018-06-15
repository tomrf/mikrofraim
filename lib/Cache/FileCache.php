<?php

namespace Mikrofraim\Cache;

class FileCache extends ArrayCache implements \Psr\SimpleCache\CacheInterface
{
    protected $cache = null;
    protected $fileCachePath = PROJECT_DIRECTORY . '/storage/cache/filecache';
    protected $cacheRead = false;

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

    public function isFileCachePathWritable()
    {
        if (! file_exists($this->fileCachePath)) {
            try {
                touch($this->fileCachePath);
            } catch (\Exception $e) {
                return false;
            }
            $this->writeCache();
        }

        return is_writable($this->fileCachePath);
    }

    public function set($key, $value, $ttl = null)
    {
        if ($this->cache === null) {
            $this->readCache();
        }

        return parent::set($key, $value, $ttl);
    }

    public function get($key, $default = null)
    {
        if ($this->cache === null) {
            $this->readCache();
        }

        return parent::get($key, $default);
    }

    public function delete($key)
    {
        if ($this->cache === null) {
            $this->readCache();
        }

        return parent::delete($key);
    }

    public function clear()
    {
        $this->cache = null;
        $this->writeCache();

        return true;
    }

}
