<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Driver;
use Bedard\Shop\Tests\Fixtures\Generate;

class DriverModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_getConfigValue()
    {
        $driver = new Driver;
        $this->assertNull($driver->getConfig('foo'));

        $driver->config = ['foo' => 'bar'];
        $this->assertEquals('bar', $driver->getConfig('foo'));

        $this->assertNull($driver->getConfig('bar'));
    }
}
