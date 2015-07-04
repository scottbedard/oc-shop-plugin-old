<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Shipping;
use Bedard\Shop\Tests\Fixtures\Generate;

class ShippingModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_getCalculator_method()
    {
        Shipping::set('behavior', 'off');
        Shipping::set('calculator', 'Bedard\Shop\Classes\ShippingTable');
        $this->assertFalse(Shipping::getCalculator());

        Shipping::set('behavior', 'on');
        $this->assertInstanceOf('Bedard\Shop\Classes\ShippingTable', Shipping::getCalculator());

        Shipping::set('calculator', false);
        $this->assertFalse(Shipping::getCalculator());
    }
}
