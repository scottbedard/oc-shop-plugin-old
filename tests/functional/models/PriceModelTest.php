<?php namespace Bedard\Shop\Tests\Functional\Models;

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

}
