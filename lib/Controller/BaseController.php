<?php

namespace Mikrofraim\Controller;

abstract class BaseController
{
    /**
     * Application container
     * @var \Mikrofraim\Application\Application
     */
    public $app;

    public function __construct(\Mikrofraim\Application\Application $app)
    {
        $this->app = $app;
    }
}
