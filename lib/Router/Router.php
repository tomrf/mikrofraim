<?php

namespace Mikrofraim;

class Router
{
    private $routes = null;
    private $prefix = null;
    private $before = null;
    private $after = null;

    public function group($prefix, $callable, $before = null, $after = null) {
        $this->prefix = $prefix;
        $this->before = $before;
        $this->after = $after;
        $callable();
        $this->prefix = null;
        $this->before = null;
        $this->after = null;
    }

    public function add($method, $route, $handler)
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
                if ($this->before) {
                    $ptr['.before'] = $this->before;
                }
                if ($this->after) {
                    $ptr['.after'] = $this->after;
                }
            }
            $prev = &$ptr;
            $ptr = &$ptr[$t];
        }
        $ptr['.handler'] = $handler;
        if ($this->before) {
            $ptr['.before'] = $this->before;
        }
        if ($this->after) {
            $ptr['.after'] = $this->after;
        }
    }

    public function route($method, $uri)
    {
        $path = $uri;
        $query = null;
        $params = [];
        $before = null;
        $after = null;

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

        foreach ($tok as $i => $dir) {
            if ($dir === '') {
                continue;
            }
            if (isset($ptr[$dir])) {
                $ptr = $ptr[$dir];
            } else if (isset($ptr['*'])) {
                $ptr = $ptr['*'];
                break;
            } else {
                $match = 0;
                foreach ($ptr as $j => $p) {
                    if ($j[0] === '.') {
                        continue;
                    }
                    if ($j[0] === '{') {
                        $ptr = $ptr[$j];
                        $params[substr($j, 1, -1)] = $dir;
                        $match++;
                    }
                }
                if (! $match) {
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

}
