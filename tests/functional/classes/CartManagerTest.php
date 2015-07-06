<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Classes\CartManager;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Settings;
use Bedard\Shop\Models\Shipping;
use Bedard\Shop\Tests\Fixtures\Generate;
use Request;

class CartManagerTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_initializing_and_opening_a_new_cart()
    {
        $first = new CartManager;
        $this->assertNull($first->cart);

        $second = new CartManager;
        $second->loadCart();
        $this->assertInstanceOf('Bedard\Shop\Models\Cart', $second->cart);

        $third = new CartManager;
        $this->assertInstanceOf('Bedard\Shop\Models\Cart', $third->cart);
        $this->assertEquals($second->cart->id, $third->cart->id);
    }

    public function test_adding_a_product()
    {
        $product    = Generate::product('Foo');
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);

        $manager = new CartManager;
        $manager->loadCart();
        $manager->addItem($inventory->id);

        $manager->cart->load('items');
        $this->assertEquals(1, $manager->cart->items->count());
        $this->assertEquals(1, $manager->cart->items->where('inventory_id', $inventory->id)->first()->quantity);

        $manager->addItem($inventory->id, [], 10);
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

        $manager = new CartManager;
        $manager->loadCart();
        $manager->addItem($inventory1->id);
        $manager->addItem($inventory2->id);

        $manager->cart->load('items');
        $this->assertEquals(2, $manager->cart->items->count());
        $one = $manager->cart->items->where('inventory_id', $inventory1->id)->first();
        $two = $manager->cart->items->where('inventory_id', $inventory2->id)->first();

        $manager->updateItems([
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

        $manager = new CartManager;
        $manager->loadCart();
        $manager->addItem($inventory1->id);
        $manager->addItem($inventory2->id);
        $manager->addItem($inventory3->id);

        $manager->cart->load('items');
        $this->assertEquals(3, $manager->cart->items->count());
        $one    = $manager->cart->items->where('inventory_id', $inventory1->id)->first();
        $two    = $manager->cart->items->where('inventory_id', $inventory2->id)->first();
        $three  = $manager->cart->items->where('inventory_id', $inventory3->id)->first();

        $manager->removeItems($one->id);
        $manager->cart->load('items');
        $this->assertEquals(2, $manager->cart->items->count());
        $this->assertEquals(0, $manager->cart->items->where('id', $one->id)->count());

        $manager->removeItems([$two->id, $three->id]);
        $manager->cart->load('items');
        $this->assertEquals(0, $manager->cart->items->count());
    }

    public function test_applying_and_removing_a_promotion()
    {
        $promotion = Generate::promotion('Foo');

        $manager = new CartManager;
        $manager->loadCart();
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

        $manager = new CartManager;
        $manager->loadCart();
        $manager->addItem($inventory1->id);
        $manager->addItem($inventory2->id);

        $manager->cart->load('items');
        $this->assertEquals(2, $manager->cart->items->count());
        $manager->clearItems();
        $manager->cart->load('items');
        $this->assertEquals(0, $manager->cart->items->count());
    }

    public function test_getItems_and_getItemCount_methods()
    {
        $product1   = Generate::product('Foo');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);

        $product2   = Generate::product('Bar');
        $inventory2 = Generate::inventory($product2, [], ['quantity' => 5]);

        $manager = new CartManager;

        $this->assertEquals(0, $manager->getItemCount());
        $this->assertEquals([], $manager->getItems());

        $manager->loadCart();
        $manager->addItem($inventory1->id, [], 2);
        $manager->addItem($inventory2->id, [], 5);

        $this->assertEquals(7, $manager->getItemCount());
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $manager->getItems());
        $this->assertEquals(2, $manager->getItems()->count());
    }

    public function test_adding_and_removing_customer_and_address_method()
    {
        $customer = [
            'first_name' => 'Some',
            'last_name' => 'Guy',
            'email' => 'some.guy@example.com',
        ];

        $address = [
            'organization'  => 'Some Company',
            'street_1'      => '123 Foo St.',
            'street_2'      => 'Apartment 2B',
            'city'          => 'Schenectady',
            'postal_code'   => '12345',
            'state_id'      => 37,
            'country_id'    => 1,
        ];

        $manager = new CartManager;
        $manager->loadCart();
        $this->assertNull($manager->cart->customer_id);
        $this->assertNull($manager->cart->address_id);

        $manager->setCustomerAddress($customer, $address);
        $manager->cart->load('customer', 'address');
        $this->assertInstanceOf('Bedard\Shop\Models\Customer', $manager->cart->customer);
        $this->assertInstanceOf('Bedard\Shop\Models\Address', $manager->cart->address);

        $manager->removeCustomer();
        $manager->cart->load('customer');
        $this->assertNull($manager->cart->customer_id);

        $manager->removeAddress();
        $manager->cart->load('address');
        $this->assertNull($manager->cart->address_id);
    }

    public function test_shipping_is_cleared_when_actions_are_completed()
    {
        $product1   = Generate::product('Foo');
        $inventory1 = Generate::inventory($product1, [], ['quantity' => 5]);

        $manager = new CartManager;
        $manager->loadCart();
        $manager->cart->shipping_rates = [];
        $manager->cart->shipping_failed = true;
        $manager->cart->save();

        $manager->addItem($inventory1->id, [], 2);
        $this->assertNull($manager->cart->shipping_rates);
        $this->assertFalse($manager->cart->shipping_failed);
    }
}
