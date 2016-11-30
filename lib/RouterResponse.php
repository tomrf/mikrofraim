<?php

class RouterResponse
{
	public $method = null;
	public $call = null;
	public $params = null;
	public $query = null;

	function __construct($method, $call, $params = null, $query = null)
	{
		$this->method = $method;
		$this->call = $call;
		$this->params = $params;
		$this->query = $query;
	}
}
