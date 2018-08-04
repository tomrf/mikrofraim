<?php

use Mikrofraim\Router;
use Mikrofraim\Log;
use Mikrofraim\Session;
use Mikrofraim\View;

/* define some helpful constants used by the framework */
define('WORKING_DIRECTORY', getcwd());
define('PROJECT_DIRECTORY', realpath(WORKING_DIRECTORY . '/../'));

/* autoload vendor packages */
require_once('../vendor/autoload.php');

/* load environment variables from .env */
if (!file_exists('../.env')) {
    die('<b>Error:</b> Environment file ".env" missing<br>Rename ".env.example" to ".env" in the project root, and edit the file as necessary.');
}
$dotenv = new Dotenv\Dotenv('../');
$dotenv->load();

/* set production mode */
if (filter_var(getenv('PRODUCTION'), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', 'Off');
} else {
    /* register whoops handler if enabled */
    if (filter_var(getenv('USE_WHOOPS'), FILTER_VALIDATE_BOOLEAN)) {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }
}

/* set timezone, default to UTC */
if (getenv('TIMEZONE')) {
    date_default_timezone_set(getenv('TIMEZONE'));
} else {
    date_default_timezone_set('UTC');
}

/* require local framework components */
require_once('../lib/Facades/Facade.php');
require_once('../lib/Facades/Route.php');
require_once('../lib/Router/Router.php');
require_once('../lib/Router/RouterResponse.php');
require_once('../lib/Helpers/Session.php');

/* session alias */
class_alias('Mikrofraim\Session', 'Session');

/* load array cache */
if (strtolower(getenv('CACHE_ENGINE')) === 'array') {
    require_once('../lib/Facades/Cache.php');
    require_once('../lib/Cache/ArrayCache.php');

    $arrayCache = new Mikrofraim\Cache\ArrayCache();
    class_alias('Mikrofraim\Facades\Cache', 'Cache');

    Cache::setInstance($arrayCache);
}

/* load filecache and ensure writable filecache file */
if (strtolower(getenv('CACHE_ENGINE')) === 'file') {
    require_once('../lib/Facades/Cache.php');
    require_once('../lib/Cache/ArrayCache.php');
    require_once('../lib/Cache/FileCache.php');

    $fileCache = new Mikrofraim\Cache\FileCache();
    if (!$fileCache->isFileCachePathWritable()) {
        throw new Exception('FileCache path not writable: storage/cache/');
    }

    class_alias('Mikrofraim\Facades\Cache', 'Cache');

    Cache::setInstance($fileCache);
    Cache::init();
}

/* load redis cache */
if (strtolower(getenv('CACHE_ENGINE')) === 'redis') {
    require_once('../lib/Facades/Cache.php');
    require_once('../lib/Cache/ArrayCache.php');
    require_once('../lib/Cache/RedisCache.php');

    $redisCache = new Mikrofraim\Cache\RedisCache();

    class_alias('Mikrofraim\Facades\Cache', 'Cache');

    Cache::setInstance($redisCache);
    Cache::init();
}

/* set up monolog */
if (filter_var(getenv('USE_MONOLOG'), FILTER_VALIDATE_BOOLEAN)) {
    require_once('../lib/Helpers/Log.php');
    class_alias('Mikrofraim\Log', 'Log');
    Log::init();
}

/* set up syslog */
if (filter_var(getenv('USE_SYSLOG'), FILTER_VALIDATE_BOOLEAN)) {
    require_once('../lib/Helpers/Syslog.php');
    class_alias('Mikrofraim\Syslog', 'Syslog');
}

/* load twig and View class */
if (filter_var(getenv('USE_TWIG'), FILTER_VALIDATE_BOOLEAN)) {
    require_once('../lib/Helpers/View.php');
    class_alias('Mikrofraim\View', 'View');
}

/* create Router instance, set up facade and load routes from ../routes.php */
$router = new Router();
class_alias('Mikrofraim\Facades\Route', 'Route');
Route::setInstance($router);
require_once('../routes.php');

/* configure ORM if env('USE_DATABASE') */
if (filter_var(getenv('USE_DATABASE'), FILTER_VALIDATE_BOOLEAN)) {
    /* sqlite */
    if (strtolower(getenv('DB_DRIVER')) === 'sqlite') {
        ORM::configure('sqlite:../' . getenv('DB_FILENAME'));
    } /* mysql */
    elseif (strtolower(getenv('DB_DRIVER')) === 'mysql') {
        ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);
        ORM::configure('id_column', 'id');
        ORM::configure("mysql:host=" . getenv('DB_HOSTNAME') . ";dbname=" . getenv('DB_DATABASE'));
        ORM::configure('username', getenv('DB_USERNAME'));
        ORM::configure('password', getenv('DB_PASSWORD'));
    } /* pgsql */
    elseif (strtolower(getenv('DB_DRIVER')) === 'pgsql') {
        ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);
        ORM::configure('id_column', 'id');
        ORM::configure("pgsql:host=" . getenv('DB_HOSTNAME') . ";dbname=" . getenv('DB_DATABASE'));
        ORM::configure('username', getenv('DB_USERNAME'));
        ORM::configure('password', getenv('DB_PASSWORD'));
    } /* unknown driver */
    else {
        throw new Exception('Unknown database driver: ' . getenv('DB_DRIVER'));
    }
}

/* autoloader function for controllers and database models */
function autoload($class)
{
    if (file_exists('../models/' . $class . '.php')) {
        require_once '../models/' . $class . '.php';
    } elseif (substr($class, 0, 6) === 'Model\\') {
        $resolvedClass = basename(str_replace('\\', '/', $class));
        require_once '../models/' . $resolvedClass . '.php';
    } elseif (file_exists('../controllers/' . $class . '.php')) {
        require_once '../controllers/' . $class . '.php';
    } elseif (file_exists('../classes/' . $class . '.php')) {
        require_once '../classes/' . $class . '.php';
    } else {
        throw new Exception('Class not found: ' . $class);
    }
}

spl_autoload_register('autoload');

/* route the request */
$response = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
if (!$response) {
    header("HTTP/1.0 404 Not Found");

    if (file_exists('../templates/404.html')) {
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

        if (file_exists('../templates/403.html')) {
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
