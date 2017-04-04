<?php
    /* autoload vendor packages */
    require_once('../vendor/autoload.php');

    /* load environment variables from .env */
    if (! file_exists('../.env')) {
        die('<b>Error:</b> Environment file ".env" missing.<br>Rename ".env.example" to ".env" in the project root, and edit the file as necessary.');
    }
    $dotenv = new Dotenv\Dotenv('../');
    $dotenv->load();

    /* set production mode */
    if (filter_var(getenv('PRODUCTION'), FILTER_VALIDATE_BOOLEAN)) {
        ini_set('display_errors','Off');
    } else {
        /* register whoops handler if enabled */
        if (filter_var(getenv('USE_WHOOPS'), FILTER_VALIDATE_BOOLEAN)) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }
    }

    /* require local framework components */
    require_once('../lib/Router.php');
    require_once('../lib/RouterResponse.php');
    require_once('../lib/View.php');
    require_once('../lib/Log.php');
    require_once('../lib/Session.php');

    /* set up monolog */
    // $log = new \Monolog\Logger('name');
    // $log->pushHandler(new \Monolog\Handler\StreamHandler('../' . getenv('LOG_FILENAME'), \Monolog\Logger::DEBUG));
    // $log->warning('Fooffff');
    // $log->error('Barrrrr');
    // $log->info('abklabkabk abk akbakbk a123 123 123');
    if (filter_var(getenv('USE_MONOLOG'), FILTER_VALIDATE_BOOLEAN)) {
        Log::init();
        Log::debug('yay');
        Log::notice('yay2');
        Log::warning('yay3');
    }

    /* create Router instance, load routes from ../routes.php */
    $router = new Router();
    require_once('../routes.php');

    /* configure ORM if env('USE_DATABASE') */
    if (filter_var(getenv('USE_DATABASE'), FILTER_VALIDATE_BOOLEAN)) {
        ORM::configure('error_mode', PDO::ERRMODE_EXCEPTION);
        ORM::configure('id_column', 'id');
        ORM::configure("mysql:host=" . getenv('DB_HOSTNAME') . ";dbname=" . getenv('DB_DATABASE'));
        ORM::configure('username', getenv('DB_USERNAME'));
        ORM::configure('password', getenv('DB_PASSWORD'));
    }

    /* autoloader function for database models */
    function autoload($class)
    {
        require_once '../models/' . $class . '.php';
    }
    spl_autoload_register('autoload');


    /* configure and start session */
    // if (getenv('SESSION_NAME')) {
    //     session_name(getenv('SESSION_NAME'));
    // }
    // session_start();

    /* route the request */
    $response = $router->route($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
    if (! $response) {
        header("HTTP/1.0 404 Not Found");
        return;
    }

    /* parse response */
    $params = $response->params;
    if ($response->query) {
        $params = array_merge($params, ['_query' => $response->query]);
    }

    /* pass through filter if set */
    if ($response->filter) {
        if (! call_user_func($response->filter)) {
            header("HTTP/1.0 403 Forbidden");
            return;
        }
    }

    /* determine handler function */
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

    /* call handler */
    echo call_user_func_array($call, $params);
