<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Settings;
use Bedard\Shop\Tests\Fixtures\Generate;
use Request;

class CartManagerTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_initializing_and_opening_a_new_cart()
    {
        $first = CartManager::open();
        $this->assertNull($first->cart);

        $second = CartManager::openOrCreate();
        $this->assertInstanceOf('Bedard\Shop\Models\Cart', $second->cart);

        $third = CartManager::open();
        $this->assertInstanceOf('Bedard\Shop\Models\Cart', $third->cart);
        $this->assertEquals($second->cart->id, $third->cart->id);
    }

    public function test_adding_a_product()
    {
        $product    = Generate::product('Foo');
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);

        $manager = CartManager::openOrCreate();
        $manager->add($inventory->id);

        $manager->cart->load('items');
        $this->assertEquals(1, $manager->cart->items->count());
        $this->assertEquals(1, $manager->cart->items->where('inventory_id', $inventory->id)->first()->quantity);

        $manager->add($inventory->id, [], 10);
        $manager->cart->load('items');
        $this->assertEquals(1, $manager->cart->items->count());
        $this->assertEquals(5, $manager->cart->items->where('inventory_id', $inventory->id)->first()->quantity);
    }

    public function test_updating_cart_items()
    {
        $product1   = Generate::product('Foo');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);

        $product2   = Generate::product('Bar');
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $manager = CartManager::openOrCreate();
        $manager->add($inventory1->id);
        $manager->add($inventory2->id);

        $manager->cart->load('items');
        $this->assertEquals(2, $manager->cart->items->count());
        $one = $manager->cart->items->where('inventory_id', $inventory1->id)->first();
        $two = $manager->cart->items->where('inventory_id', $inventory2->id)->first();

        $manager->update([
            $one->id => 3,
            $two->id => 10,
        ]);

        $manager->cart->load('items');
        $this->assertEquals(2, $manager->cart->items->count());
        $this->assertEquals(3, $manager->cart->items->where('id', $one->id)->first()->quantity);
        $this->assertEquals(5, $manager->cart->items->where('id', $two->id)->first()->quantity);
    }

    public function test_removing_cart_items()
    {
        $product1   = Generate::product('Foo');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);

        $product2   = Generate::product('Bar');
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $product3   = Generate::product('Baz');
        $inventory3 = Generate::inventory($product3, [], ['quantity' => 5]);

        $manager = CartManager::openOrCreate();
        $manager->add($inventory1->id);
        $manager->add($inventory2->id);
        $manager->add($inventory3->id);

        $manager->cart->load('items');
        $this->assertEquals(3, $manager->cart->items->count());
        $one    = $manager->cart->items->where('inventory_id', $inventory1->id)->first();
        $two    = $manager->cart->items->where('inventory_id', $inventory2->id)->first();
        $three  = $manager->cart->items->where('inventory_id', $inventory3->id)->first();

        $manager->remove($one->id);
        $manager->cart->load('items');
        $this->assertEquals(2, $manager->cart->items->count());
        $this->assertEquals(0, $manager->cart->items->where('id', $one->id)->count());

        $manager->remove([$two->id, $three->id]);
        $manager->cart->load('items');
        $this->assertEquals(0, $manager->cart->items->count());
    }

    public function test_applying_and_removing_a_promotion()
    {
        $promotion = Generate::promotion('Foo');

        $manager = CartManager::openOrCreate();
        $this->assertEquals(null, $manager->cart->promotion_id);

        $manager->applyPromotion('Foo');
        $this->assertEquals($promotion->id, $manager->cart->promotion_id);

        $manager->removePromotion();
        $this->assertEquals(null, $manager->cart->promotion_id);
    }

    public function test_clearing_the_entire_cart()
    {
        $product1   = Generate::product('Foo');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);

        $product2   = Generate::product('Bar');
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $manager = CartManager::openOrCreate();
        $manager->add($inventory1->id);
        $manager->add($inventory2->id);

        $manager->cart->load('items');
        $this->assertEquals(2, $manager->cart->items->count());
        $manager->clear();
        $manager->cart->load('items');
        $this->assertEquals(0, $manager->cart->items->count());
    }
}
