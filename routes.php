<?php

    /* example route showing optional route arguments and closure handler */
    $router->routeAdd('GET', '/test/{val?}', function($val = 'default') {
        return "val = $val";
    });

    /* default home route with controller class handler */
    $router->routeAdd('GET', '/', 'HomeController@index');
