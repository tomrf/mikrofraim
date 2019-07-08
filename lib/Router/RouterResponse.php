<?php

namespace Mikrofraim\Router;

class RouterResponse
{
    /**
     * Route method (GET, POST, ...)
     * @var null|string
     */
    private $method = null;

    /**
     * Route callable
     * @var mixed
     */
    private $call = null;

    /**
     * Route paramenters
     * @var null|array
     */
    private $params = null;

    /**
     * Route query
     * @var string
     */
    private $query = null;

    /**
     * Before filter
     * @var mixed
     */
    private $before = null;

    /**
     * After filter
     * @var mixed
     */
    private $after = null;

    /**
     * Get route method
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get route callable
     * @return mixed
     */
    public function getCall()
    {
        return $this->call;
    }

    /**
     * Get route parameters
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get route query
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get before filter
     * @return mixed
     */
    public function getBefore()
    {
        return $this->before;
    }

    /**
     * Get after filter
     * @return mixed
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @param string $method
     * @param mixed $call
     * @param mixed $params
     * @param mixed $query
     * @param mixed $before
     * @param mixed $after
     */
    public function __construct(
        string $method,
        $call,
        $params = null,
        $query = null,
        $before = null,
        $after = null
    ) {
        $this->method = $method;
        $this->call = $call;
        $this->params = $params;
        $this->query = $query;
        $this->before = $before;
        $this->after = $after;
    }
}
