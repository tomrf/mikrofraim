<?php

class Router
{
    private $routes = null;
    private $prefix = null;
    private $filter = null;

    public function group($prefix, $callable, $filter = null) {
        $this->prefix = $prefix;
        $this->filter = $filter;
        $callable();
        $this->prefix = null;
        $this->filter = null;
    }

    public function routeAdd($method, $route, $handler)
    {
        if ($this->prefix) {
            $route = $this->prefix . $route;
        }
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }
        $ptr = &$this->routes[$method];
        $prev = null;
        $tok = explode('/', $route);
        foreach ($tok as $t) {
            if ($t === '') {
                continue;
            }
            if (!isset($ptr[$t])) {
                $ptr[$t] = [];
            }
            if (strstr(key($ptr), '?}')) {
                $ptr['.handler'] = $handler;
                if ($this->filter) {
                    $ptr['.filter'] = $this->filter;
                }
            }
            $prev = &$ptr;
            $ptr = &$ptr[$t];
        }
        $ptr['.handler'] = $handler;
        if ($this->filter) {
            $ptr['.filter'] = $this->filter;
        }
    }

    public function route($method, $uri)
    {
        $path = $uri;
        $query = null;
        $params = [];
        $filter = null;

        if (strstr($uri, '?')) {
            $tok = explode('?', $uri);
            $path = $tok[0];
            $query = $tok[1];
        }

        if (! isset($this->routes[$method])) {
            return null;
        }

        $ptr = $this->routes[$method];

        $tok = explode('/', $path);
        foreach ($tok as $t) {
            if ($t === '') {
                continue;
            }
            $key = key($ptr);
            if ($key[0] === '{') {
                $ptr = $ptr[$key];
                $key = substr($key, 1, -1);
                $params[$key] = $t;
            } else {
                if (! isset($ptr[$t])) {
                    return null;
                }
                $ptr = $ptr[$t];
            }
        }
        if (isset($ptr['.filter'])) {
            $filter = $ptr['.filter'];
        }
        if (isset($ptr['.handler'])) {
            return new RouterResponse($method, $ptr['.handler'], $params, $query, $filter);
        }
        return null;
    }

    public function debugPrintRoutes()
    {
        var_dump($this->routes);
    }
}
