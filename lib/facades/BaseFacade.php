<?php

namespace Mikrofraim\Facade;

class BaseFacade
{
    private static $instance = null;

    public static function setInstance($instance)
    {
        self::$instance = $instance;
    }

    public static function __callStatic($name, $arguments)
    {
        if (self::$instance === null) {
            throw new \RuntimeException('No instance set');
        }

        if (! method_exists(self::$instance, $name)) {
            throw new \RuntimeException('Method does not exist');
        }

        return call_user_func_array([self::$instance, $name], $arguments);
    }

}
