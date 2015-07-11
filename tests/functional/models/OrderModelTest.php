<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Driver;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\OrderEvent;
use Bedard\Shop\Models\Status;
use Bedard\Shop\Tests\Fixtures\Generate;
use Carbon\Carbon;

class OrderModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_changing_order_status_creates_order_event()
    {
        $driver     = Driver::create(['name' => 'Foo']);
        $order      = Order::create([]);

        $this->assertNull($order->status_id);
        $this->assertEquals(0, OrderEvent::where('order_id', $order->id)->count());

        $order->changeStatus(1, $driver);
        $this->assertEquals(1, $order->status_id);
        $this->assertEquals(1, OrderEvent::where('order_id', $order->id)->count());

        $order->changeStatus(1, $driver);
        $this->assertEquals(1, OrderEvent::where('order_id', $order->id)->count());
    }
}
