<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Discount;
use Bedard\Shop\Models\Price;
use Bedard\Shop\Tests\Fixtures\Generate;
use Carbon\Carbon;

class DiscountModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * When a discount is saved, it should make a price model for every
     * product within it's scope.
     */
    public function test_correct_price_models_are_created()
    {
        $parent     = Generate::category('Parent');
        $child      = Generate::category('Child', ['parent_id' => $parent->id]);
        $grandchild = Generate::category('Grandchild', ['parent_id' => $child->id]);
        $unrelated  = Generate::category('Unrelated');

        $first      = Generate::product('First');
        $second     = Generate::product('Second');
        $third      = Generate::product('Third');
        $fourth     = Generate::product('Fourth');

        $first->categories()->add($parent);
        $second->categories()->add($grandchild);
        $third->categories()->add($unrelated);
        $fourth->categories()->add($unrelated);

        // Discount only the third product
        $discount = Generate::discount('Hat only');
        $discount->products()->add($third);
        $discount->load('products');
        $discount->save();
        $discount->load('prices');
        $this->assertEquals(1, $discount->prices->count());
        $this->assertEquals(1, $discount->prices->where('product_id', $third->id)->count());

        // Discount the unrelated category, shoes should be discounted
        $discount->categories()->add($unrelated);
        $discount->load('categories.inherited', 'categories.inherited_by');
        $discount->save();
        $discount->load('prices');
        $this->assertEquals(2, $discount->prices->count());
        $this->assertEquals(1, $discount->prices->where('product_id', $third->id)->count());
        $this->assertEquals(1, $discount->prices->where('product_id', $fourth->id)->count());

        // Discount the parent category, and everything should become discounted
        $discount->categories()->add($parent);
        $discount->load('categories.inherited', 'categories.inherited_by');
        $discount->save();
        $discount->load('prices');
        $this->assertEquals(4, $discount->prices->count());
        $this->assertEquals(1, $discount->prices->where('product_id', $third->id)->count());
        $this->assertEquals(1, $discount->prices->where('product_id', $fourth->id)->count());
        $this->assertEquals(1, $discount->prices->where('product_id', $first->id)->count());
        $this->assertEquals(1, $discount->prices->where('product_id', $second->id)->count());
    }

    /**
     * Discount a product, and make sure it's price is correct
     */
    public function test_discount_pricing_is_correct()
    {
        $product = Generate::product('Foo', ['base_price' => 10]);
        $discount = Generate::discount('Bar', [
            'is_percentage' => true,
            'amount_percentage' => 10
        ]);

        $discount->products()->add($product);
        $discount->load('products');
        $discount->save();

        $product->load('current_price');
        $this->assertEquals(9, $product->current_price->price);

        $discount->is_percentage = false;
        $discount->amount_exact = 5;
        $discount->save();

        $product->load('current_price');
        $this->assertEquals(5, $product->current_price->price);
    }

    /**
     * Make sure prices are being calculated correctly
     */
    public function test_calculate_price()
    {
        $percentage = Generate::discount('Foo', ['is_percentage' => true, 'amount_percentage' => 25]);
        $this->assertEquals(75, $percentage->calculate(100)); // 100 - 25% = 75

        $exact = Generate::discount('Foo', ['is_percentage' => false, 'amount_exact' => 35]);
        $this->assertEquals(65, $exact->calculate(100)); // 100 - 35 = 65
    }

    /**
     * Make sure syncAllProducts works
     */
    public function test_syncronizing_all_products()
    {
        $inactive = Generate::discount('Inactive', [
            'end_at'        => Carbon::yesterday(),
            'is_percentage' => false,
            'amount_exact'  => 50,
        ]);

        $active = Generate::discount('Active', [
            'is_percentage'  => false,
            'amount_exact'  => 25,
        ]);

        $upcoming = Generate::discount('Upcoming', [
            'start_at'      => Carbon::tomorrow(),
            'is_percentage' => false,
            'amount_exact'  => 10,
        ]);

        $product = Generate::product('Product', ['base_price' => 100]);
        $product->discounts()->sync([$inactive->id, $active->id, $upcoming->id]);

        $product->load('discounted_prices');
        $this->assertEquals(0, $product->discounted_prices->count());

        Discount::syncAllProducts();
        $product->load('discounted_prices');
        $this->assertEquals(2, $product->discounted_prices->count());
        $this->assertEquals(1, $product->discounted_prices->where('discount_id', $active->id)->count());
        $this->assertEquals(1, $product->discounted_prices->where('discount_id', $upcoming->id)->count());
    }

    public function test_discount_status_methods()
    {
        $discount = Generate::discount('Discount');
        $this->assertTrue($discount->isRunning());
        $this->assertFalse($discount->isExpired());
        $this->assertFalse($discount->isUpcoming());

        $discount->start_at = Carbon::yesterday();
        $this->assertTrue($discount->isRunning());
        $this->assertFalse($discount->isExpired());
        $this->assertFalse($discount->isUpcoming());

        $discount->end_at = Carbon::tomorrow();
        $this->assertTrue($discount->isRunning());
        $this->assertFalse($discount->isExpired());
        $this->assertFalse($discount->isUpcoming());

        $discount->start_at = Carbon::tomorrow();
        $discount->end_at = null;
        $this->assertFalse($discount->isRunning());
        $this->assertFalse($discount->isExpired());
        $this->assertTrue($discount->isUpcoming());

        $discount->end_at = Carbon::tomorrow()->addDay(1);
        $this->assertFalse($discount->isRunning());
        $this->assertFalse($discount->isExpired());
        $this->assertTrue($discount->isUpcoming());

    }
}
