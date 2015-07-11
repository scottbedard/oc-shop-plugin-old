<?php namespace Bedard\Shop\Tests\Functional\Classes;

use Bedard\Shop\Classes\PaymentProcessor;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\PaymentSettings;
use Bedard\Shop\Tests\Fixtures\Generate;

class PaymentProcessorTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_immediate_timing_and_canceling_behavior()
    {
        $cart       = Generate::cart();
        $product    = Generate::product('Shirt');
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);
        $cartItem   = Generate::cartItem($cart, $inventory, ['quantity' => 2]);

        $settings = PaymentSettings::instance();
        $settings->timing       = 'immediate';
        $settings->success_url  = 'http://localhost/success';
        $settings->canceled_url = 'http://localhost/canceled';
        $settings->error_url    = 'http://localhost/error';
        $settings->save();

        // When we hit begin, the inventory should be reduced
        $processor = new PaymentProcessor($cart);
        $processor->begin();
        $this->assertEquals(3, Inventory::find($inventory->id)->quantity);
        $cart = Cart::find($cart->id);
        $this->assertEquals('paying', $cart->status);
        $this->assertTrue($cart->is_inventoried);

        // If we hit it again, nothing should change
        $processor = new PaymentProcessor($cart);
        $processor->begin();
        $cart = Cart::find($cart->id);
        $this->assertEquals(3, Inventory::find($inventory->id)->quantity);
        $this->assertTrue($cart->is_inventoried);

        // If we cancel the payment the inventory should return
        $processor->cancel();
        $cart = Cart::find($cart->id);
        $this->assertEquals('canceled', $cart->status);
        $this->assertEquals(5, Inventory::find($inventory->id)->quantity);
        $this->assertFalse($cart->is_inventoried);

        // Lastly, if we cancel again nothing should change
        $processor = new PaymentProcessor($cart);
        $cart = Cart::find($cart->id);
        $processor->cancel();
        $this->assertEquals(5, Inventory::find($inventory->id)->quantity);
        $this->assertFalse($cart->is_inventoried);
    }

    public function test_completed_timing_behavior()
    {
        $cart       = Generate::cart();
        $product    = Generate::product('Shirt');
        $inventory  = Generate::inventory($product, [], ['quantity' => 5]);
        $cartItem   = Generate::cartItem($cart, $inventory, ['quantity' => 2]);

        $settings = PaymentSettings::instance();
        $settings->timing       = 'completed';
        $settings->success_url  = 'http://localhost/success';
        $settings->canceled_url = 'http://localhost/canceled';
        $settings->error_url    = 'http://localhost/error';
        $settings->save();

        // Beginning the payment should not remove inventory
        $processor = new PaymentProcessor($cart);
        $processor->begin();
        $this->assertEquals(5, Inventory::find($inventory->id)->quantity);
        $this->assertFalse(Cart::find($cart->id)->is_inventoried);

        // // Completing the payment should remove inventory
        $processor->complete();
        $this->assertEquals(3, Inventory::find($inventory->id)->quantity);
        $this->assertTrue(Cart::find($cart->id)->is_inventoried);
    }

    public function test_order_is_inserted()
    {
        $cart       = Generate::cart();
        $product    = Generate::product('Foo');
        $inventory  = Generate::inventory($product, [], ['quantity' => 10]);
        $item       = Generate::cartItem($cart, $inventory, ['quantity' => 5]);

        $processor = new PaymentProcessor($cart);
        $processor->complete();

        $this->assertEquals(1, Order::where('cart_id', $cart->id)->count());
    }
}
