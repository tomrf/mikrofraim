<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MikrofraimTestBase extends TestCase
{
    /**
     *
     * @var \Mikrofraim\Router\Router
     */
    protected static $router;

    /**
     * @var \Mikrofraim\Application\Application
     */
    protected static $app;

    public static function setUpBeforeClass()
    {
        if (!isset(self::$app)) {
            self::$app = require_once('bootstrap/bootstrap.php');
            self::$router = self::$app->getComponent('router');
        }
    }

    protected function checkIfRouterResponseIsValid($routerResponse): void
    {
        $this->assertInstanceOf(\Mikrofraim\Router\RouterResponse::class, $routerResponse);
        $this->assertTrue($this->isCallableOrValidCallableString($routerResponse->getCall()), '$routerResponse->getCall() not valid');
        $this->assertTrue($this->isCallableOrNull($routerResponse->getBefore()), '$routerResponse before filter is not a callable or null');
        $this->assertTrue($this->isCallableOrNull($routerResponse->getAfter()), '$routerResponse after filter is not a callable or null');
    }

    protected function isCallableOrNull($c)
    {
        return $c === null || is_callable($c);
    }

    protected function isCallableOrValidCallableString($call)
    {
        if (is_callable($call)) {
            return true;
        }

        if (strstr($call, '@') && count(explode('@', $call)) === 2) {
            return true;
        }

        return false;
    }

    protected function getRouterResponse(string $method, string $uri)
    {
        return self::$router->route($method, $uri);
    }

    /**
     * Stolen from public/index.php, should probably exist in RouterReponse to
     * facilitate reuse.
     *
     * @param type $routerResponse
     * @throws Exception
     */
    protected function getCallableFromRouterResponse($routerResponse)
    {
        /* determine handler function */
        if (is_string($routerResponse->getCall())) {
            if (strstr($routerResponse->getCall(), '@')) {
                $call = explode('@', $routerResponse->getCall());
                $callClass = 'Controllers\\' . $call[0];
                $callFunc = $call[1];
                $controller = new $callClass(self::$app);
                return [$controller, $callFunc];
            } else {
                return $routerResponse->getCall();
            }
        } else {
            return $routerResponse->getCall();
        }
    }

    /**
     * Get the raw response from a controller method, without before- or after-
     * filters
     *
     * @param string $method
     * @param string $uri
     */
    protected function callRouteWithoutFilters(string $method, string $uri)
    {
        $routerResponse = $this->getRouterResponse($method, $uri);
        $callable = $this->getCallableFromRouterResponse(
                        $this->getRouterResponse($method, $uri));
        return call_user_func_array($callable, $routerResponse->getParams());
    }
}




