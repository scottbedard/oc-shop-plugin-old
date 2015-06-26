<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Tests\Fixtures\Generate;

class CartManagerTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_count_items()
    {
        $cart = new CartManager;

        $this->assertEquals(0, $cart->getItemCount());
        $this->assertNull($cart->cart);

        $product1   = Generate::product('Foo');
        $product2   = Generate::product('Bar');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $cart->add($product1->id, [], 1);
        $cart->add($product2->id, [], 2);
        $this->assertEquals(3, $cart->getItemCount());
    }

    public function test_cart_prevents_over_adding()
    {
        $cart = new CartManager;

        $product    = Generate::product('Foo');
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);

        $cart->add($product->id, [], 10);
        $this->assertEquals(5, $cart->getItemCount());
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

        // todo: update the item
    }

    public function test_removing_items()
    {
        $product1   = Generate::product('Foo');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);

        $product2   = Generate::product('Bar');
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $product3   = Generate::product('Baz');
        $inventory3 = Generate::inventory($product3, [], ['quantity' => 5]);

        $cart = new CartManager;
        $cart->add($product1->id, [], 1);
        $cart->add($product2->id, [], 1);
        $cart->add($product3->id, [], 1);

        $this->assertEquals(3, CartItem::where('cart_id', $cart->cart->id)->count());

        // Remove the first item by itself
        $cart->remove($product1->id);
        $this->assertEquals(1, CartItem::where('cart_id', $cart->cart->id)->where('id', $product2->id)->count());
        $this->assertEquals(1, CartItem::where('cart_id', $cart->cart->id)->where('id', $product3->id)->count());
        $this->assertEquals(2, CartItem::where('cart_id', $cart->cart->id)->count());

        // Remove the last two items together
        $cart->remove([$product2->id, $product3->id]);
        $this->assertEquals(0, CartItem::where('cart_id', $cart->cart->id)->count());
    }

    public function test_get_subtotal()
    {
        $product1   = Generate::product('Foo', ['base_price' => 10]);
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);

        $product2   = Generate::product('Bar', ['base_price' => 5]);
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $cart = new CartManager;
        $this->assertEquals(0, $cart->getSubtotal());

        $cart->add($product1->id, [], 2);
        $cart->add($product2->id, [], 1);

        $this->assertEquals(25, $cart->getSubtotal());

        // todo: make sure promotion value isn't factored in
    }
}
