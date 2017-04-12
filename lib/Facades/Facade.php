<?php

namespace Mikrofraim\Facades;

abstract class Facade
{
    protected static $instance = [];

    public static function setInstance($instance)
    {
        static::$instance[static::getFacadeName()] = $instance;
    }

    public static function getInstance()
    {
        if (! isset(static::$instance[static::getFacadeName()])) {
            throw new \RuntimeException('Instance does not exist');
        }

        return static::$instance[static::getFacadeName()];
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = static::getInstance();

        if ($instance === null) {
            throw new \RuntimeException('No instance set');
        }

        if (! method_exists($instance, $name)) {
            throw new \RuntimeException('Method does not exist');
        }

        return call_user_func_array([ $instance, $name ], $arguments);
    }

}
