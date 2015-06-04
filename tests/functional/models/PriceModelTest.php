<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Price;
use Bedard\Shop\Tests\Fixtures\Generate;

class PriceModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * Make sure that a default value is set. SQLite can get tripped
     * up without these model events
     */
    public function test_empty_price()
    {
        $product = Generate::product('Foo');
        $price = Generate::price($product, '', ['discount_id' => 1]);
        $this->assertEquals($price->price, 0);
    }

    /**
     * Make sure a negative price is converted to zero
     */
    public function test_negative_prices_are_zero()
    {
        $price = new Price;
        $price->price = 5;
        $this->assertEquals(5, $price->price);

        $price->price = -8;
        $this->assertEquals(0, $price->price);
    }

}
