<?php

/* bootstrap the application */
$app = require_once __DIR__.'/../bootstrap/bootstrap.php';

/* get router instance */
$router = $app->getComponent('router');

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
$params = $response->getParams();
if ($response->getQuery()) {
    $params = array_merge($params, ['_query' => $response->getQuery()]);
}

/* pass through before filter if set */
if ($response->getBefore()) {
    if (call_user_func($response->getBefore()) !== true) {
        header("HTTP/1.0 403 Forbidden");

        if (file_exists(__DIR__.'/../templates/403.html')) {
            echo view::render('403.html');
        }

        return;
    }
}

/* determine handler function */
if (is_string($response->getCall())) {
    if (strstr($response->getCall(), '@') !== false) {
        $call = explode('@', $response->getCall());
        $callClass = 'Controllers\\' . $call[0];
        $callFunc = $call[1];

        if (!method_exists($callClass, $callFunc)) {
            throw new Exception('Method does not exist: ' . $response->getCall());
        }

        /* create instance of controller */
        $controller = new $callClass($app);

        $call = [$controller, $callFunc];
    } else {
        $call = $response->getCall();
    }
} else {
    $call = $response->getCall();
}

/* call handler */
if ($response->getAfter() !== null) {
    $handlerReturn = call_user_func_array($call, $params);
    echo call_user_func_array($response->getAfter(), [$handlerReturn]);
} else {
    echo call_user_func_array($call, $params);
}

/* unset redirect data */
if (Session::get('_isRedirect') === false) {
    Session::delete('_redirectData');
} elseif (Session::get('_isRedirect') === true) {
    Session::set('_isRedirect', false);
}
