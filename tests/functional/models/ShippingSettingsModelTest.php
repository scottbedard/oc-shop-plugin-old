<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\ShippingSettings;
use Bedard\Shop\Tests\Fixtures\Generate;

class ShippingSettingsModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_getCalculator_method()
    {
        $cart = Generate::cart();

        ShippingSettings::set('behavior', 'off');
        ShippingSettings::set('calculator', 'Bedard\Shop\Drivers\Shipping\BasicTable');
        $this->assertFalse(ShippingSettings::getCalculator($cart));

        ShippingSettings::set('behavior', 'on');
        $this->assertInstanceOf('Bedard\Shop\Drivers\Shipping\BasicTable', ShippingSettings::getCalculator($cart));

        ShippingSettings::set('calculator', false);
        $this->assertFalse(ShippingSettings::getCalculator($cart));
    }
}
