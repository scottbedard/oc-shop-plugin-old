<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\ShippingSettings;
use Bedard\Shop\Tests\Fixtures\Generate;

class ShippingSettingsModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_getCalculator_method()
    {
        $cart = Generate::cart();
        $driver = 'Bedard\Shop\Drivers\Shipping\BasicTable';

        ShippingSettings::set('calculator', null);
        $this->assertFalse(ShippingSettings::getCalculator());
        $this->assertFalse(ShippingSettings::getCalculator($cart));

        ShippingSettings::set('calculator', $driver);
        $this->assertEquals($driver, ShippingSettings::getCalculator());
        $this->assertInstanceOf($driver, ShippingSettings::getCalculator($cart));
    }
}
