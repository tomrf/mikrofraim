<?php

use Mikrofraim\Router;
use Mikrofraim\Log;
use Mikrofraim\Session;
use Mikrofraim\View;

/* load bootstrap.php */
require_once __DIR__.'/../bootstrap/bootstrap.php';

/* route the request */
$response = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
if (!$response) {
    header("HTTP/1.0 404 Not Found");

    if (file_exists(__DIR__.'/../templates/404.html')) {
        echo View::render('404.html');
    }

    return;
}

/* parse response */
$params = $response->params;
if ($response->query) {
    $params = array_merge($params, ['_query' => $response->query]);
}

/* pass through before filter if set */
if ($response->before) {
    if (!call_user_func($response->before)) {
        header("HTTP/1.0 403 Forbidden");

        if (file_exists(__DIR__.'/../templates/403.html')) {
            echo view::render('403.html');
        }

        return;
    }
}

/* determine handler function */
if (is_string($response->call)) {
    if (strstr($response->call, '@')) {
        $call = explode('@', $response->call);
        $callClass = $call[0];
        $callFunc = $call[1];

        if (!method_exists($callClass, $callFunc)) {
            throw new Exception('Method does not exist: ' . $response->call);
        }

        $call = [$callClass, $callFunc];
    } else {
        $call = $response->call;
    }
} else {
    $call = $response->call;
}

/* call handler */
if (isset($response->after)) {
    $handlerReturn = call_user_func_array($call, $params);
    echo call_user_func_array($response->after, [$handlerReturn]);
} else {
    echo call_user_func_array($call, $params);
}
