<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\Status;
use Bedard\Shop\Tests\Fixtures\Generate;
use Carbon\Carbon;

class OrderModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_changing_order_status()
    {
        $received   = Status::create(['name' => 'Received']);
        $canceled   = Status::create(['name' => 'Canceled']);
        $order      = Order::create([]);

        $order->load('events.status');
        $this->assertEquals(1, $order->status->id);

        $order->changeStatus(1);
        $order->load('events.status');
        $this->assertEquals(1, $order->events->count());

        $order->changeStatus(2);
        $order->load('events.status');
        $this->assertEquals(2, $order->events->count());
    }
}
