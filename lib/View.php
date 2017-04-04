<?php

class View
{
    private static $twigLoader = null;
    private static $twig = null;

    public static function init()
    {
        self::$twigLoader = new Twig_Loader_Filesystem('../templates/');
        self::$twig = new Twig_Environment(self::$twigLoader, array(
            'cache' => filter_var(getenv('TWIG_USE_CACHE'), FILTER_VALIDATE_BOOLEAN),
            'debug' => filter_var(getenv('TWIG_DEBUG'), FILTER_VALIDATE_BOOLEAN)
        ));
        self::$twig->addExtension(new Twig_Extension_Debug());
    }

    public static function render($template, $data = null)
    {
        self::init();
        if ($data == null) {
            $data = [ ];
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
