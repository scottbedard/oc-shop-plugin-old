<?php namespace Bedard\Shop\Tests\Functional\Traits;

use Bedard\Shop\Tests\Fixtures\Generate;
use Bedard\Shop\Models\Price;
use Carbon\Carbon;

class DateActiveTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_is_active_scopes()
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

        $active = Price::isActive()->get();
        $this->assertEquals($active->count(), 3);
        $this->assertEquals($active->where('price', $active_1->price)->count(), 1);
        $this->assertEquals($active->where('price', $active_2->price)->count(), 1);
        $this->assertEquals($active->where('price', $active_3->price)->count(), 1);

        $activeOrUpcoming = Price::isActiveOrUpcoming()->get();
        $this->assertEquals($activeOrUpcoming->count(), 5);
        $this->assertEquals($activeOrUpcoming->where('price', $active_1->price)->count(), 1);
        $this->assertEquals($activeOrUpcoming->where('price', $active_2->price)->count(), 1);
        $this->assertEquals($activeOrUpcoming->where('price', $active_3->price)->count(), 1);
        $this->assertEquals($activeOrUpcoming->where('price', $upcoming_1->price)->count(), 1);
        $this->assertEquals($activeOrUpcoming->where('price', $upcoming_2->price)->count(), 1);
    }
}
