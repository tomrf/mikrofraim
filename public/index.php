<?php
    require_once('../vendor/autoload.php');

    require_once('../lib/View.php');
    require_once('../lib/Router.php');
    require_once('../lib/RouterResponse.php');

    $router = new Router();
    require_once('../routes.php');

    $response = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    if (! $response) {
        header("HTTP/1.0 404 Not Found");
        return;
    }

    $params = $response->params;
    if ($response->query) {
        $params = array_merge($params, ['_query' => $response->query]);
    }

    if (is_string($response->call)) {
        if (strstr($response->call, '@')) {
            $call = explode('@', $response->call);
            $callClass = $call[0];
            $callFunc = $call[1];

            if (! method_exists($callClass, $callFunc)) {
                echo "ERROR: Method does not exist: {$response->call}";
                return;
            }

            $call = [ $callClass, $callFunc ];
        } else {
            $call = $response->call;
        }
    } else {
        $call = $response->call;
    }

    echo call_user_func_array($call, $params);
