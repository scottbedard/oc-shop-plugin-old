<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Driver;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\OrderEvent;
use Bedard\Shop\Models\PaymentSettings;
use Bedard\Shop\Models\Status;
use Bedard\Shop\Tests\Fixtures\Generate;
use Carbon\Carbon;

class OrderModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_changing_order_status_creates_order_event()
    {
        $driver     = Driver::create(['name' => 'Foo']);
        $status     = Status::create(['name' => 'Bar']);
        $order      = Order::create([]);

        $this->assertNull($order->status_id);
        $this->assertEquals(0, OrderEvent::where('order_id', $order->id)->count());

        $order->changeStatus($status, $driver);
        $this->assertEquals($status->id, $order->status_id);
        $this->assertEquals(1, OrderEvent::where('order_id', $order->id)->count());

        $order->changeStatus($status, $driver);
        $this->assertEquals(1, OrderEvent::where('order_id', $order->id)->count());
    }

    public function test_should_be_abandoned_scope()
    {
        $started = Status::getCore('started');
        $order = Order::create(['status_id' => $started->id, 'is_paid' => false]);

        $order->status_at = Carbon::now()->subMinutes(360);
        $order->save();
        $this->assertEquals(0, Order::shouldBeAbandoned(360)->count());

        $order->status_at = Carbon::now()->subMinutes(361);
        $order->save();
        $this->assertEquals(1, Order::shouldBeAbandoned(360)->count());

        $order->status_id = Status::getCore('received')->id;
        $order->save();
        $this->assertEquals(0, Order::shouldBeAbandoned(360)->count());
    }
}
