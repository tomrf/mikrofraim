<?php

/**
 * These are the internal tests of the mikrofraim framework. These are designed
 * to break for a user project, because it makes assumtions about the default
 * route, etc
 *
 * @author Fredrik
 */
class MikrofraimFrameworkTest extends MikfrofraimTestBase
{
    /**
     * Test that default route contains a title and a link to readme.md
     */
    public function testMikrofraimDefaultRoute()
    {
        $defaultRouteHtml = $this->callRouteWithoutFilters('GET', '/');
        $this->assertContains('<a href="https://github.com/tomrf/mikrofraim/blob/master/readme.md">readme</a>', $defaultRouteHtml);
        $this->assertContains('<title> Ready! </title>', $defaultRouteHtml);
    }

    public function testThatUnknownURIReturnsNull(): void
    {
        $this->assertNull($this->getRouterResponse('GET', '/non_existing_route', [], [], []));
    }

    public function testThatMainUriReturnsValidRouterResponse()
    {
        $routerResponse = $this->getRouterResponse('GET', '/');
        $this->checkIfRouterResponseIsValid($routerResponse);
    }

    public function testSetup()
    {
        $this->assertInstanceOf(\Mikrofraim\Router::class, $this->router);
    }
}
