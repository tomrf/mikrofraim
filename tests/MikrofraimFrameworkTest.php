<?php

/**
 * These are the internal tests of the mikrofraim framework. These are designed
 * to break for a user project, because it makes assumtions about the default
 * route, etc
 *
 * @author Fredrik
 */
class MikrofraimFrameworkTest extends MikrofraimTestBase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
    }

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
        $this->assertNull($this->getRouterResponse('GET', '/non_existing_route'));
    }

    public function testThatMainUriReturnsValidRouterResponse()
    {
        $routerResponse = $this->getRouterResponse('GET', '/');
        $this->checkIfRouterResponseIsValid($routerResponse);
    }

    public function testSetup()
    {
        $this->assertInstanceOf(\Mikrofraim\Router\Router::class, self::$router);
    }

    protected function createTables()
    {
        $db = ORM::get_db();
        $db->exec('
            DROP TABLE IF EXISTS berries;
            CREATE TABLE berries (
                name string,
                c integer
            );
        ');
        $blueBerry = ORM::for_table('berries')->create();
        $blueBerry->name = 'blue';
        $blueBerry->c = 10;
        $blueBerry->save();

        $redBerry = ORM::for_table('berries')->create();
        $redBerry->name = 'red';
        $redBerry->c = 20;
        $redBerry->save();
    }

    public function testBerries()
    {
        $this->createTables();
        $berries = ORM::for_table('berries')
            ->order_by_asc('c')
            ->find_many();
        $this->assertEquals(2, count($berries));

        $this->assertEquals('blue', $berries[0]->name);
        $this->assertEquals('red', $berries[1]->name);
    }
}
