<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\ShippingMethod;
use Bedard\Shop\Models\ShippingRate;
use Bedard\Shop\Tests\Fixtures\Generate;

class ShippingRateModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_selecting_rates_by_weight()
    {
        $method1 = Generate::shippingMethod('Foo1');
        $method2 = Generate::shippingMethod('Foo2', ['min_weight' => 5]);
        $method3 = Generate::shippingMethod('Foo3', ['min_weight' => 5, 'max_weight' => 10]);
        $method4 = Generate::shippingMethod('Foo4', ['max_weight' => 20]);

        $rate1 = Generate::shippingRate($method1);
        $rate2 = Generate::shippingRate($method2);
        $rate3 = Generate::shippingRate($method3);
        $rate4 = Generate::shippingRate($method4);

        $query = ShippingRate::whereWeight(0)->get();
        $this->assertEquals(2, $query->count());
        $this->assertEquals(1, $query->where('id', $method1->id)->count());
        $this->assertEquals(1, $query->where('id', $method4->id)->count());

        $query = ShippingRate::whereWeight(7)->get();
        $this->assertEquals(4, $query->count());

        $query = ShippingRate::whereWeight(15)->get();
        $this->assertEquals(3, $query->count());
        $this->assertEquals(1, $query->where('id', $method1->id)->count());
        $this->assertEquals(1, $query->where('id', $method2->id)->count());
        $this->assertEquals(1, $query->where('id', $method4->id)->count());
    }
}
