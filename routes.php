<?php

/* example route showing optional route arguments and closure handler */
Route::add('GET', '/test/{val?}', function ($val = 'default') {
    return "val = $val";
});

/* default home route with controller class handler */
Route::add('GET', '/', 'HomeController@index');
