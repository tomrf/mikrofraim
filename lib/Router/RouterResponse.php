<?php

class RouterResponse
{
    public $method = null;
    public $call   = null;
    public $params = null;
    public $query  = null;
    public $before = null;
    public $after  = null;

    function __construct($method, $call, $params = null, $query = null, $before = null, $after = null)
    {
        $this->method = $method;
        $this->call   = $call;
        $this->params = $params;
        $this->query  = $query;
        $this->before = $before;
        $this->after  = $after;
    }
}
