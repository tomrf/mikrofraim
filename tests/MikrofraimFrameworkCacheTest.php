<?php

use Mikrofraim\Facades\Cache;

/**
 * Description of MikrofraimFrameworkCacheTest
 *
 * @author Fredrik
 */
class MikrofraimFrameworkCacheTest extends \Cache\IntegrationTests\SimpleCacheTest
{
    public function setUp()
    {
        require_once('bootstrap/bootstrap.php');
        parent::setUp();
    }

    public function createSimpleCache()
    {
        return Cache::getInstance();
    }
}
