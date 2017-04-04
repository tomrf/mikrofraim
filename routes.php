<?php
    $router->routeAdd('GET', '/test/{val?}', function($val = 'default') { return "val=$val"; });

    $router->routeAdd('GET', '/', function() {
        return View::render('home.html');
    });
