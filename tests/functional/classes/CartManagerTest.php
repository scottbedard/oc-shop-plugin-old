<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Tests\Fixtures\Generate;

class CartManagerTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_count_items()
    {
        $cart = new CartManager;

        $this->assertEquals(0, $cart->itemCount());
        $this->assertNull($cart->cart);

        $product1   = Generate::product('Foo');
        $product2   = Generate::product('Bar');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $cart->add($product1->id, [], 1);
        $cart->add($product2->id, [], 2);
        $this->assertEquals(3, $cart->itemCount());
    }

    public function test_cart_prevents_over_adding()
    {
        $cart = new CartManager;

        $product    = Generate::product('Foo');
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);

        $cart->add($product->id, [], 10);
        $this->assertEquals(5, $cart->itemCount());
    }

    public function test_adding_and_updating_items()
    {
        $product = Generate::product('Foo');
        $inventory = Generate::inventory($product, [], ['quantity' => 5]);

        $cart = new CartManager;
        $cart->add($product->id, [], 1);
        $cart->add($product->id, [], 1);

        $items = $cart->getItems();
        $this->assertEquals(1, $items->where('product_id', $product->id)->count());
    }
}
