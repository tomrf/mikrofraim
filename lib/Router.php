<?php

class Router
{
	private $routes = null;

	public function routeAdd($method, $route, $handler)
	{
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
			}
			$prev = &$ptr;
			$ptr = &$ptr[$t];
		}
		$ptr['.handler'] = $handler;
	}

	public function route($method, $uri)
	{
		$path = $uri;
		$query = null;
		$params = [];

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
		if (isset($ptr['.handler'])) {
			return new RouterResponse($method, $ptr['.handler'], $params, $query);
		}
		return null;
	}

	public function debugPrintRoutes()
	{
		var_dump($this->routes);
	}
}
