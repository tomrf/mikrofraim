<?php

namespace Mikrofraim\Application;

class Application
{
    /**
     * Components
     * @var array
     */
    private $components;

    /**
     * Components for deferred loading
     * @var array
     */
    private $componentsDeferred;

    /**
     * Register a component
     * @param  string   $name
     * @param  callable $content
     */
    public function registerComponent(string $name, callable $content): void
    {
        $this->components[$name] = call_user_func($content);
    }

    /**
     * Register a component for deferred loading
     * @param  string   $name
     * @param  callable $content
     */
    public function registerComponentDeferred(string $name, callable $content): void
    {
        $this->componentsDeferred[$name] = $content;
    }

    /**
     * Return a component
     * @param  string $name
     * @return callable|null
     */
    public function getComponent(string $name)
    {
        if (isset($this->components[$name])) {
            return $this->components[$name];
        } elseif (isset($this->componentsDeferred[$name])) {
            $this->registerComponent($name, $this->componentsDeferred[$name]);
            unset($this->componentsDeferred[$name]);
            return $this->components[$name];
        }
        return null;
    }

    /**
     * Load environment variables from file
     * @param  string $file
     */
    public function loadEnvironment(string $file): void
    {
        $fh = fopen($file, 'r');

        if ($fh === false) {
            throw new \Exception('Failed to open environment file "' . $file . '"');
        }

        while (!feof($fh)) {
            $line = fgets($fh);

            if ($line === false) {
                continue;
            }

            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if ($line[0] === '#') {
                continue;
            }
            $parts = explode('=', $line);
            $key = $parts[0];
            $value = str_replace('"', '', $parts[1]);
            $cutOff = strpos($value, '#');
            if ($cutOff !== false) {
                $value = trim(substr($value, 0, $cutOff));
            }
            putenv($key . '=' . $value);
        }
        fclose($fh);
    }

    /**
     * Proxy method access to getComponent()
     * @param  string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->getComponent($name);
    }
}
