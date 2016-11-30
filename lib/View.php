<?php

class View
{
    private static $twigLoader = null;
    private static $twig = null;

    public static function init()
    {
        self::$twigLoader = new Twig_Loader_Filesystem('../templates/');
        self::$twig = new Twig_Environment(self::$twigLoader, array(
            'cache' => false,
            'auto_reload' => true,
            'debug' => true
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

}
