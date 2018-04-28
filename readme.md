## Mikrofraim

Mikrofraim is a minimalist PHP framework for websites, APIs etc.

Fast internal array based routing with support for routing groups and authentication filters.

We also include a few 3rd party components;

- [Twig](https://github.com/twigphp/Twig) - Flexible, fast, and secure template engine for PHP
- [Whoops](https://github.com/filp/whoops) - PHP errors for cool kids
- [Monolog](https://github.com/Seldaek/monolog) - Powerful and flexible logging for PHP
- [PHP dotenv](https://github.com/vlucas/phpdotenv) - Loads environment variables from .env
- [Idiorm and Paris](http://j4mie.github.io/idiormandparis/) - A minimalist database toolkit for PHP5

## Requirements

PHP 7.x

## Installation

Clone the repository, do a "composer update", copy .env.example to .env in the root folder and ensure writable permissions on storage/.

## Framework structure
```
lib/            Internal framework library classes, for facades, caching, routing and helpers
models/         Paris ORM models (totally optional), autoloaded
controller/     Classic MVC controller functions, autoloaded
classes/        Custom classes for your app, not created by default, but will be autoloaded from if it exists
public/         The public web root, contains index.php responsible for bootstrapping the framework
templates/      Twig template files, for easy rendering via the View::render() framework helper
storage/        Temporary storage area for logs and filebased caching (if enabled)
routes.php      Route definitions
composer.json   Composer project dependencies
```

## Routing

All routes live in `routes.php` by default, where you can add simple routes and more complex routing groups.

Add routes with `Route::add()` which accept three arguments; **request verb** (GET, POST, ...), **path** and **handler function**

**Request verb**

Any valid HTTP request verb, or `*` to match all request types.

## Routing examples
### Simple routing
##### Optional argument (val), closure handler, direct output
***/routes.php***
```
Route::add('GET', '/test/{val?}', function($val = 'default') {
    return "val = $val";
});
```
##### Required argument (id), controller handler, templated output
***/routes.php***
```
Route::add('GET', '/user/{id}/list', 'UserController@list');
```
***/controllers/UserController.php***
```
Class UserController {
    public function list() {
        $list = [...];
        return View::render('list.html', [ 'list' => $list ]);
    }
}
```
### Routing group with URL prefix (/prefix) and pre and post request filter
***/routes.php***
```
Route::group('/prefix', function() {
    Route::add('GET', '/resource', 'ResourceController@resource');
}, function() {
    // this filter is called before routing
    // return false to deny the request
    // return true to accept it

    if (Session::get('authenticated') !== true) {
        return false;
    }

    return true;
}, function($response) {
    // this filter is called after routing and handling the request, but before data is returned to client
    // $repsonse contains any returned data from handling function and can be manipulated freely before being returned to client

    if (! $response) {
        return View::render('error.html', [ 'errorMessage' => 'internal error, no data ' ]);
    }

    return $response;
});
```

## Logging example
The class alias 'Log' is referencing Mikrofraim\Log, a simple helper interface to the powerful Monolog.
### Simple logging
```
Log::info('informative message');
Log::warning('something is wrong');
```
This would result in the following messages written to /storage/logs/debug.log
```
2017-06-29 01:07:23 > INFO > informative message
2017-06-29 01:07:23 > WARNING > something is wrong
```
Our helper class has easy to use interfaces for debug(), info(), notice(), warning() and error() levels of logging.

Access the full power of Monolog directly by accessing the \Monolog namespace.

## Caching example
Mikrofraim ships with a redis cache interface, as well as two basic cache implementations, ArrayCache (non-persistent in-memory storage lasting the lifetime of the request) and the FileCache, which works as an ArrayCache but writes and loads content from disk during requests, for long term data persistance.

Our cache interface follows the PSR-16 simple-cache standard.

Configure which cache engine to use in the environment file (.env)

##### Simple set with 60 second TTL
```
Cache::set('cache-key', 123, 60);
```

##### Delete cache key
```
Cache::delete('cache-key');
```

##### Clear cache
```
Cache::clear();
```

## Twig templates
Templates resides, per default, in `templates/`

You can create custom 40x views for 404 and 403 responses by adding a `404.html` and `403.html` template in the templates directory. 

Please refer to the official Twig documentation to learn about the twig syntax and features.
## License

Mikrofraim is licensed under a BEERWARE license scheme