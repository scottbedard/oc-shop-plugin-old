<?php namespace Bedard\Shop\Tests\Functional\Classes;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Drivers\Shipping\BasicTable;
use Bedard\Shop\Models\ShippingSettings;
use Bedard\Shop\Tests\Fixtures\Generate;

class ShippingTableTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['RainLab.Location', 'Bedard.Shop'];

    public function test_getRates_method()
    {
        ShippingSettings::set('behavior', 'on');
        ShippingSettings::set('calculator', 'Bedard\Shop\Drivers\Shipping\BasicTable');

        // Create a shopping cart with an address
        $product    = Generate::product('Product', ['base_price' => 10, 'weight' => 10]);
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);
        $address    = Generate::address([
            'street_1'      => '123 Foo St.',
            'street_2'      => 'Apartment 2B',
            'city'          => 'Schanectady',
            'postal_code'   => '12345',
            'state_id'      => 37,
            'country_id'    => 1,
        ]);

        // Generate some rates
        $method1 = Generate::shippingMethod('Standard');
        $rate1 = Generate::shippingRate($method1, ['base_price' => 1]);
        $rate2 = Generate::shippingRate($method1, ['base_price' => 2]);
        $rate3 = Generate::shippingRate($method1, ['base_price' => 0]);
        $rate1->countries()->sync([1]);
        $rate2->countries()->sync([1]);
        $rate3->countries()->sync([1]);
        $rate3->states()->sync([1]);

        $method2 = Generate::shippingMethod('Priority');
        $rate4 = Generate::shippingRate($method2, ['base_price' => 4]);
        $rate5 = Generate::shippingRate($method2, ['base_price' => 5]);
        $rate4->countries()->sync([2]);
        $rate5->countries()->sync([1]);

        $manager = new CartManager;
        $manager->addItem($product->id);
        $manager->loadItemData(true);
        $manager->cart->shipping_address_id = $address->id;
        $manager->cart->save();
        $manager->cart->load('shipping_address');

        $table = new BasicTable();
        $table->setCart($manager->cart);
        $rates = $table->getRates();

        // 1 & 5 should be returned
        $this->assertEquals(1, $rates[0]['cost']);
        $this->assertEquals(5, $rates[1]['cost']);
    }
}
