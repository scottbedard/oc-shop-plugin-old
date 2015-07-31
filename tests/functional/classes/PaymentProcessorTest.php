<?php namespace Bedard\Shop\Tests\Functional\Classes;

use Bedard\Shop\Classes\PaymentProcessor;
use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\PaymentSettings;
use Bedard\Shop\Models\Status;
use Bedard\Shop\Tests\Fixtures\Generate;
use Carbon\Carbon;

class PaymentProcessorTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

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
