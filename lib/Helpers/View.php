<?php

namespace Mikrofraim;

class View
{
    private static $twigLoader = null;
    private static $twig = null;
    private static $cachePath = '../storage/cache/twig';

    public static function init()
    {
        $cache = false;
        if (filter_var(getenv('TWIG_USE_CACHE'), FILTER_VALIDATE_BOOLEAN)) {
            $cache = self::$cachePath;
        }
        self::$twigLoader = new \Twig_Loader_Filesystem('../templates/');
        self::$twig = new \Twig_Environment(self::$twigLoader, array(
            'cache' => $cache,
            'debug' => filter_var(getenv('TWIG_DEBUG'), FILTER_VALIDATE_BOOLEAN)
        ));
        self::$twig->addGlobal('session', isset($_SESSION) ? $_SESSION : null);
        self::$twig->addGlobal('server', isset($_SERVER) ? $_SERVER : null);
        self::$twig->addExtension(new \Twig_Extension_Debug());
    }

    public static function render($template, $data = null)
    {
        self::init();
        if ($data == null) {
            $data = [];
        }
        return self::$twig->render($template, $data);
    }

    public static function redirect($path, $code = null)
    {
        if ($code) {
            header('HTTP/1.1 ' . $code);
        }
        header('Location: ' . $path);
    }

}
