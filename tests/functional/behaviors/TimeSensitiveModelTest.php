<?php namespace Bedard\Shop\Tests\Functional\Traits;

use Bedard\Shop\Tests\Fixtures\Generate;
use Bedard\Shop\Models\Price;
use Carbon\Carbon;

class TimeSensitiveModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_status_accessors()
    {
        $discount = Generate::discount('Discount');
        $this->assertTrue($discount->isRunning);
        $this->assertFalse($discount->isExpired);
        $this->assertFalse($discount->isUpcoming);

        $discount->start_at = Carbon::yesterday();
        $this->assertTrue($discount->isRunning);
        $this->assertFalse($discount->isExpired);
        $this->assertFalse($discount->isUpcoming);

        $discount->end_at = Carbon::tomorrow();
        $this->assertTrue($discount->isRunning);
        $this->assertFalse($discount->isExpired);
        $this->assertFalse($discount->isUpcoming);

        $discount->start_at = Carbon::tomorrow();
        $discount->end_at = null;
        $this->assertFalse($discount->isRunning);
        $this->assertFalse($discount->isExpired);
        $this->assertTrue($discount->isUpcoming);

        $discount->end_at = Carbon::tomorrow()->addDay(1);
        $this->assertFalse($discount->isRunning);
        $this->assertFalse($discount->isExpired);
        $this->assertTrue($discount->isUpcoming);
    }

    public function test_status_scopes()
    {
        $product = Generate::product('Foo', ['base_price' => 1]);

        // Active prices
        $active_1 = $product->current_price;
        $active_2 = Generate::price($product, 2, [
            'discount_id'   => 2,
            'start_at'      => Carbon::yesterday()
        ]);
        $active_3 = Generate::price($product, 3, [
            'discount_id'   => 3,
            'start_at'      => Carbon::yesterday(),
            'end_at'        => Carbon::tomorrow(),
        ]);

        // Expired prices
        $expired_1 = Generate::price($product, 4, ['discount_id' => 4, 'end_at' => Carbon::yesterday()]);
        $expired_2 = Generate::price($product, 5, [
            'discount_id'   => 5,
            'start_at'      => Carbon::yesterday()->subDay(),
            'end_at'        => Carbon::yesterday(),
        ]);

        // Upcoming
        $upcoming_1 = Generate::price($product, 6, ['discount_id' => 6, 'start_at' => Carbon::tomorrow()]);
        $upcoming_2 = Generate::price($product, 7, [
            'discount_id'   => 7,
            'start_at'      => Carbon::tomorrow(),
            'end_at'        => Carbon::tomorrow()->addDay(),
        ]);

        $active = Price::isRunning()->get();
        $this->assertEquals(3, $active->count());
        $this->assertEquals(1, $active->where('price', $active_1->price)->count());
        $this->assertEquals(1, $active->where('price', $active_2->price)->count());
        $this->assertEquals(1, $active->where('price', $active_3->price)->count());

        $upcoming = Price::isUpcoming()->get();
        $this->assertEquals(2, $upcoming->count());
        $this->assertEquals(1, $upcoming->where('price', $upcoming_1->price)->count());
        $this->assertEquals(1, $upcoming->where('price', $upcoming_2->price)->count());

        $activeOrUpcoming = Price::isRunningOrUpcoming()->get();
        $this->assertEquals(5, $activeOrUpcoming->count());
        $this->assertEquals(1, $activeOrUpcoming->where('price', $active_1->price)->count());
        $this->assertEquals(1, $activeOrUpcoming->where('price', $active_2->price)->count());
        $this->assertEquals(1, $activeOrUpcoming->where('price', $active_3->price)->count());
        $this->assertEquals(1, $activeOrUpcoming->where('price', $upcoming_1->price)->count());
        $this->assertEquals(1, $activeOrUpcoming->where('price', $upcoming_2->price)->count());

        $expired = Price::isExpired()->get();
        $this->assertEquals(2, $expired->count());
        $this->assertEquals(1, $expired->where('price', $expired_1->price)->count());
        $this->assertEquals(1, $expired->where('price', $expired_2->price)->count());
    }
}
