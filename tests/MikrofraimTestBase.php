<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class MikfrofraimTestBase extends TestCase
{
    /**
     *
     * @var \Mikrofraim\Router
     */
    protected $router;

    public function setUp()
    {
        require_once('bootstrap/bootstrap.php');
        $this->router = Route::getInstance('router');
    }

    protected function checkIfRouterResponseIsValid($routerResponse): void
    {
        $this->assertInstanceOf(\Mikrofraim\RouterResponse::class, $routerResponse);
        $this->assertTrue($this->isCallableOrValidCallableString($routerResponse->call), '$routerResponse->call not valid');
        $this->assertTrue($this->isCallableOrNull($routerResponse->before), '$routerResponse before filter is not a callable or null');
        $this->assertTrue($this->isCallableOrNull($routerResponse->after), '$routerResponse after filter is not a callable or null');
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

        if (strstr($call, '@') && count(explode('@', $call)) == 2) {
            return true;
        }

        return false;
    }

    protected function getRouterResponse(string $method, string $uri)
    {
        return $this->router->route($method, $uri);
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
        if (is_string($routerResponse->call)) {
            if (strstr($routerResponse->call, '@')) {
                $call = explode('@', $routerResponse->call);
                $callClass = $call[0];
                $callFunc = $call[1];

//                if (!method_exists($callClass, $callFunc)) {
//                    throw new Exception('Method does not exist: ' . $routerResponse->call);
//                }

                return [$callClass, $callFunc];
            } else {
                return $routerResponse->call;
            }
        } else {
            return $routerResponse->call;
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
        $routerReponse = $this->getRouterResponse($method, $uri);
        $callable = $this->getCallableFromRouterResponse(
                        $this->getRouterResponse($method, $uri));
        return call_user_func($callable, $routerReponse->params);
    }
}




