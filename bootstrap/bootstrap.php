<?php

use Mikrofraim\Application\Application;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\Redis\RedisCachePool;

/* Autoload */
require_once __DIR__.'/../vendor/autoload.php';

/* Create Application instance */
$app = new Application();

/* Load environment variables from .env */
$app->loadEnvironment($_ENV['MIKROFRAIM_ENVIRONMENT_PATH'] ?? __DIR__.'/../.env');

/* Set production mode */
if (filter_var(getenv('PRODUCTION'), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('display_errors', 'Off');
} else {
    /* register whoops handler if enabled */
    if (filter_var(getenv('USE_WHOOPS'), FILTER_VALIDATE_BOOLEAN)
        && php_sapi_name() !== 'cli') {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }
}

/* Set timezone, default to UTC */
date_default_timezone_set('UTC');
if (is_string(getenv('TIMEZONE'))) {
    date_default_timezone_set(getenv('TIMEZONE'));
}

/* Register component: view */
$app->registerComponentDeferred('view', function () {
    if (filter_var(getenv('USE_TWIG'), FILTER_VALIDATE_BOOLEAN)) {
        return new Mikrofraim\Helpers\View(
            __DIR__ . '/../templates',
            filter_var(getenv('TWIG_DEBUG'), FILTER_VALIDATE_BOOLEAN),
            filter_var(getenv('TWIG_USE_CACHE'), FILTER_VALIDATE_BOOLEAN)
        );
    } else {
        return null;
    }
});

/* Register component: log */
$app->registerComponentDeferred('log', function () {
    /* set up monolog */
    if (filter_var(getenv('USE_MONOLOG'), FILTER_VALIDATE_BOOLEAN)) {
        return new Mikrofraim\Helpers\Log(
            __DIR__ . '/../' . getenv('LOG_FILENAME'),
            "%datetime% > %level_name% > %message%\n"
        );
    } else {
        return null;
    }
});

/* Register component: syslog */
$app->registerComponentDeferred('syslog', function () {
    /* set up syslog */
    if (filter_var(getenv('USE_SYSLOG'), FILTER_VALIDATE_BOOLEAN)) {
        return new Mikrofraim\Helpers\Syslog(
            getenv('SYSLOG_IDENT') !== false ? getenv('SYSLOG_IDENT') : 'php'
        );
    } else {
        return null;
    }
});

/* Register component: session */
$app->registerComponentDeferred('session', function () {
    return new Mikrofraim\Helpers\Session(
        getenv('SESSION_NAME') !== false ? getenv('SESSION_NAME') : null
    );
});


/* Register component: form */
$app->registerComponentDeferred('form', function () {
    return new Mikrofraim\Helpers\Form;
});

/* Register component: router */
$app->registerComponentDeferred('router', function () {
    return new Mikrofraim\Router\Router();
});

/* Register component: cache */
if (getenv('CACHE_ENGINE') === 'array') {
    $pool = new ArrayCachePool();
    $simpleCache = new SimpleCacheBridge($pool);
} elseif (getenv('CACHE_ENGINE') === 'file') {
    $filesystemAdapter = new Local(__DIR__.'/../storage/cache/filecache');
    $filesystem = new Filesystem($filesystemAdapter);
    $pool = new FilesystemCachePool($filesystem);
    $simpleCache = new SimpleCacheBridge($pool);
} elseif (getenv('CACHE_ENGINE') === 'redis') {
    /* create redis connection */
    $client = new \Redis();

    $redisHost = '127.0.0.1';
    if (getenv('CACHE_REDIS_HOSTNAME') !== false) {
        $redisHost = getenv('CACHE_REDIS_HOSTNAME');
    }

    $redisPort = 6379;
    if (getenv('CACHE_REDIS_PORT') !== false) {
        $redisPort = intval(getenv('CACHE_REDIS_PORT'));
    }

    if (!$client->connect($redisHost, $redisPort)) {
        throw new \Exception('Redis connection failed');
    }

    if (getenv('CACHE_REDIS_PASSWORD') !== false
        && getenv('CACHE_REDIS_PASSWORD') !== '') {
        if (!$client->auth(getenv('CACHE_REDIS_PASSWORD'))) {
            throw new \Exception('Redis authentication failed');
        }
    }

    /* create redis cache pool */
    $pool = new RedisCachePool($client);
    $simpleCache = new SimpleCacheBridge($pool);
}

if (isset($simpleCache)) {
    $app->registerComponentDeferred('cache', function () use ($simpleCache) {
        return $simpleCache;
    });
}

/* Configure ORM if enabled */
if (filter_var(getenv('USE_DATABASE'), FILTER_VALIDATE_BOOLEAN)) {
    /* sqlite */
    if (getenv('DB_DRIVER') === 'sqlite') {
        if (getenv('DB_FILENAME') === ':memory:') {
            $sqlitePrefix = '';
        } else {
            $sqlitePrefix = '../';
            if (defined('MIKROFRAIM_TESTSUITE')) {
                $sqlitePrefix = './';
            }
        }
        ORM::configure('sqlite:' . $sqlitePrefix . getenv('DB_FILENAME'));
    } /* mysql */
    elseif (getenv('DB_DRIVER') === 'mysql') {
        ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);
        ORM::configure('id_column', 'id');
        ORM::configure('mysql:host=' . getenv('DB_HOSTNAME')
            . ';dbname=' . getenv('DB_DATABASE')
            . ';charset=' . getenv('DB_ENCODING'));
        ORM::configure('username', getenv('DB_USERNAME'));
        ORM::configure('password', getenv('DB_PASSWORD'));
    } /* pgsql */
    elseif (getenv('DB_DRIVER') === 'pgsql') {
        ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);
        ORM::configure('id_column', 'id');
        ORM::configure('pgsql:host=' . getenv('DB_HOSTNAME')
            . ';dbname=' . getenv('DB_DATABASE')
            . ';charset=' . getenv('DB_ENCODING'));
        ORM::configure('username', getenv('DB_USERNAME'));
        ORM::configure('password', getenv('DB_PASSWORD'));
    } /* unknown driver */
    else {
        throw new Exception('Unknown database driver: ' . getenv('DB_DRIVER'));
    }
}

/* Settings for paris */
Model::$short_table_names = true;

/* Set up facades */
$facades = [
    'view' => 'Mikrofraim\Facades\View',
    'log' => 'Mikrofraim\Facades\Log',
    'form' => 'Mikrofraim\Facades\Form',
    'session' => 'Mikrofraim\Facades\Session',
    'syslog' => 'Mikrofraim\Facades\Syslog',
    'cache' => 'Mikrofraim\Facades\Cache',
    'router' => 'Mikrofraim\Facades\Route'
];

foreach ($facades as $component => $class) {
    $parts = explode('\\', $class);
    $alias = end($parts);

    if ($alias === false) {
        continue;
    }

    class_alias($class, $alias);

    forward_static_call_array(
        [ $alias, 'setInstance' ],
        [ function () use ($app, $component, $alias) {
            $appComponent = $app->getComponent($component);

            forward_static_call_array(
                [ $alias, 'setInstance' ],
                [ $appComponent ]
            );

            return $appComponent;
        } ]
    );
}

/* Generate csrf token if enabled */
if (filter_var(getenv('GENERATE_CSRF'), FILTER_VALIDATE_BOOLEAN)) {
    Session::set('csrfTokenPrevious', Session::get('csrfToken'));
    Session::set('csrfToken', bin2hex(random_bytes(32)));
}

/* Load routes */
require_once __DIR__.'/../routes.php';

/* Return application instance */
return $app;
