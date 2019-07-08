<?php

namespace Mikrofraim\Router;

class Router
{
    /**
     * Route definitions
     * @var null|array
     */
    private $routes = null;

    /**
     * Route prefix
     * @var null|string
     */
    private $prefix = null;

    /**
     * Before route filter
     * @var null|callable
     */
    private $before = null;

    /**
     * After route filter
     * @var null|callable
     */
    private $after = null;

    /**
     * @param  string $prefix
     * @param  callable $callable
     * @param  mixed $before
     * @param  mixed $after
     */
    public function group(string $prefix, callable $callable, $before = null, $after = null): void
    {
        $this->prefix = $prefix;
        $this->before = $before;
        $this->after = $after;
        $callable();
        $this->prefix = null;
        $this->before = null;
        $this->after = null;
    }

    /**
     * Add a route
     * @param string $method
     * @param string $route
     * @param mixed $handler
     */
    public function add(string $method, string $route, $handler): void
    {
        if ($this->prefix !== null) {
            $route = $this->prefix . $route;
        }
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }
        $ptr = &$this->routes[$method];
        $tokens = explode('/', $route);
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }
            if (!isset($ptr[$token])) {
                $ptr[$token] = [];
            }
            if (strstr(key($ptr), '?}') !== false) {
                $ptr['.handler'] = $handler;
                if ($this->before !== null) {
                    $ptr['.before'] = $this->before;
                }
                if ($this->after !== null) {
                    $ptr['.after'] = $this->after;
                }
            }
            $ptr = &$ptr[$token];
        }
        $ptr['.handler'] = $handler;
        if ($this->before !== null) {
            $ptr['.before'] = $this->before;
        }
        if ($this->after !== null) {
            $ptr['.after'] = $this->after;
        }
    }

    /**
     * Preform routing
     * @param  string $method
     * @param  string $uri
     * @return null|object
     */
    public function route(string $method, string $uri): ?object
    {
        $path = $uri;
        $query = null;
        $params = [];
        $before = null;
        $after = null;

        if (strstr($uri, '?') !== false) {
            $tokens = explode('?', $uri);
            $path = $tokens[0];
            $query = $tokens[1];
        }

        if (!isset($this->routes[$method])) {
            return null;
        }

        $ptr = $this->routes[$method];
        $tokens = explode('/', $path);

        foreach ($tokens as $dir) {
            if ($dir === '') {
                continue;
            }
            if (isset($ptr[$dir])) {
                $ptr = $ptr[$dir];
            } elseif (isset($ptr['*'])) {
                $ptr = $ptr['*'];
                break;
            } else {
                $match = 0;
                $keys = array_keys($ptr);
                foreach ($keys as $key) {
                    $key = strval($key);
                    if ($key[0] === '.') {
                        continue;
                    }
                    if ($key[0] === '{') {
                        $ptr = $ptr[$key];
                        $params[substr($key, 1, -1)] = $dir;
                        $match++;
                    }
                }
                if ($match === 0) {
                    return null;
                }
            }
        }

        if (isset($ptr['.before'])) {
            $before = $ptr['.before'];
        }

        if (isset($ptr['.after'])) {
            $after = $ptr['.after'];
        }
        if (isset($ptr['.handler'])) {
            return new RouterResponse($method, $ptr['.handler'], $params, $query, $before, $after);
        }
        return null;
    }

    /**
     * Return routes
     * @return array|null
     */
    public function getRoutes()
    {
        return $this->routes;
    }
}
