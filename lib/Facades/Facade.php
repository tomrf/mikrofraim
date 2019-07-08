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
        $facadeName = static::getFacadeName();
        if (!isset(static::$instance[$facadeName])) {
            throw new \RuntimeException('Instance does not exist');
        }

        $instance = static::$instance[$facadeName];

        if (is_callable($instance)) {
            static::$instance[$facadeName] = call_user_func($instance);
        }

        return static::$instance[$facadeName];
    }

    public static function __callStatic($name, $arguments)
    {
        $instance = static::getInstance();

        if ($instance === null) {
            throw new \RuntimeException('No instance set for this facade');
        }

        if (!method_exists($instance, $name)) {
            throw new \RuntimeException('Method does not exist');
        }

        return call_user_func_array([$instance, $name], $arguments);
    }
}
