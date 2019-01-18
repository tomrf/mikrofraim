<?php

use Mikrofraim\Router;
use Mikrofraim\Log;
use Mikrofraim\Session;

/* define some helpful constants used by the framework */
define('WORKING_DIRECTORY', getcwd());
define('PROJECT_DIRECTORY', realpath(WORKING_DIRECTORY) . '/..');

/* autoload vendor packages */
require_once __DIR__.'/../vendor/autoload.php';

/* load environment variables from .env */
$dotEnvPath = $_ENV['MIKROFRAIM_ENVIRONMENT_PATH'] ?? __DIR__.'/../.env';
if (!file_exists($dotEnvPath)) {
    throw new Exception('Environment file "' . $dotEnvPath . '" missing, copy it from ".env.example"');
}
$dotenv = new Dotenv\Dotenv(dirname($dotEnvPath), basename($dotEnvPath));
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
require_once __DIR__.'/../lib/Facades/Facade.php';
require_once __DIR__.'/../lib/Facades/Route.php';
require_once __DIR__.'/../lib/Router/Router.php';
require_once __DIR__.'/../lib/Router/RouterResponse.php';
require_once __DIR__.'/../lib/Helpers/Session.php';

/* session alias */
class_alias('Mikrofraim\Session', 'Session');

/* create csrf token if enabled */
if (filter_var(getenv('GENERATE_CSRF'), FILTER_VALIDATE_BOOLEAN)) {
    require_once __DIR__.'/../lib/Helpers/Form.php';
    class_alias('Mikrofraim\Form', 'Form');
    Session::set('csrfTokenPrevious', Session::get('csrfToken'));
    Session::set('csrfToken', bin2hex(random_bytes(32)));
}

/* load array cache */
if (strtolower(getenv('CACHE_ENGINE')) === 'array') {
    require_once __DIR__.'/../lib/Facades/Cache.php';
    require_once __DIR__.'/../lib/Cache/ArrayCache.php';

    $arrayCache = new Mikrofraim\Cache\ArrayCache();
    class_alias('Mikrofraim\Facades\Cache', 'Cache');

    Cache::setInstance($arrayCache);
}

/* load filecache and ensure writable filecache file */
if (strtolower(getenv('CACHE_ENGINE')) === 'file') {
    require_once __DIR__.'/../lib/Facades/Cache.php';
    require_once __DIR__.'/../lib/Cache/ArrayCache.php';
    require_once __DIR__.'/../lib/Cache/FileCache.php';

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
    require_once __DIR__.'../lib/Facades/Cache.php';
    require_once __DIR__.'../lib/Cache/ArrayCache.php';
    require_once __DIR__.'../lib/Cache/RedisCache.php';

    $redisCache = new Mikrofraim\Cache\RedisCache();

    class_alias('Mikrofraim\Facades\Cache', 'Cache');

    Cache::setInstance($redisCache);
    Cache::init();
}

/* set up monolog */
if (filter_var(getenv('USE_MONOLOG'), FILTER_VALIDATE_BOOLEAN)) {
    require_once __DIR__.'/../lib/Helpers/Log.php';
    class_alias('Mikrofraim\Log', 'Log');
    Log::init();
}

/* set up syslog */
if (filter_var(getenv('USE_SYSLOG'), FILTER_VALIDATE_BOOLEAN)) {
    require_once __DIR__.'/../lib/Helpers/Syslog.php';
    class_alias('Mikrofraim\Syslog', 'Syslog');
}

/* load twig and View class */
if (filter_var(getenv('USE_TWIG'), FILTER_VALIDATE_BOOLEAN)) {
    require_once __DIR__.'/../lib/Helpers/View.php';
    class_alias('Mikrofraim\View', 'View');
}

/* create Router instance, set up facade and load routes from ../routes.php */
$router = new Router();
class_alias('Mikrofraim\Facades\Route', 'Route');
Route::setInstance($router);
require_once __DIR__.'/../routes.php';

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
        ORM::configure('driver_options', [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . getenv('DB_ENCODING')]);
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
    if (file_exists(__DIR__.'/../models/' . $class . '.php')) {
        require_once __DIR__.'/../models/' . $class . '.php';
    } elseif (substr($class, 0, 6) === 'Model\\') {
        $resolvedClass = basename(str_replace('\\', '/', $class));
        require_once __DIR__.'/../models/' . $resolvedClass . '.php';
    } elseif (file_exists(__DIR__.'/../controllers/' . $class . '.php')) {
        require_once __DIR__.'/../controllers/' . $class . '.php';
    } elseif (file_exists(__DIR__.'/../classes/' . $class . '.php')) {
        require_once __DIR__.'/../classes/' . $class . '.php';
    }
}

spl_autoload_register('autoload');
