<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Tests\Fixtures\Generate;
use Bedard\Shop\Models\Price;
use Bedard\Shop\Models\Product;
use Carbon\Carbon;
use DB;

class ProductModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * Make sure that a default value is set. SQLite can get tripped
     * up without these model events
     */
    public function test_empty_base_price()
    {
        $product = Generate::product('Foo', ['base_price' => '']);
        $this->assertNotNull($product->id);
        $this->assertEquals($product->base_price, 0);
    }

    /**
     * Markdown descriptions should be cached and stored in the
     * description_html column
     */
    public function test_caching_description_markdown()
    {
        $product = Generate::product('Bar', ['description' => '# Hello']);
        $this->assertEquals('<h1>Hello</h1>', $product->description_html);
    }

    /**
     * Pivot tables should be kept clean when a product is deleted
     */
    public function test_pivot_tables_are_kept_clean()
    {
        $category   = Generate::category('Category');
        $product    = Generate::product('Product');

        $product->categories()->add($category);
        $product->load('categories');
        $this->assertNotNull(DB::table('bedard_shop_category_product')->where('product_id', $product->id)->first());

        $product->delete();
        $this->assertNull(DB::table('bedard_shop_category_product')->where('product_id', $product->id)->first());
    }

    /**
     * Keep the price table clean
     */
    public function test_prices_are_removed_after_delete()
    {
        $product = Generate::product('Hello');
        $this->assertEquals(1, Price::where('product_id', $product->id)->count());

        $product->delete();
        $this->assertEquals(0, Price::where('product_id', $product->id)->count());
    }

    /**
     * Makes sure that the isActive() and isNotActive() scopes work
     */
    public function test_isActive_and_isNotActive_scopes()
    {
        $active     = Generate::product('Active');
        $disabled   = Generate::product('Disabled', ['is_active' => false]);

        $isActive = Product::isActive()->first();
        $this->assertEquals($active->id, $isActive->id);

        $isNotActive = Product::isNotActive()->first();
        $this->assertEquals($disabled->id, $isNotActive->id);
    }

    /**
     * Ensure that the correct price is being related to the product
     */
    public function test_price_relationship_is_working()
    {
        $product    = Generate::product('Hello', ['base_price' => 5]);
        $this->assertEquals($product->current_price->price, 5);

        $first      = Generate::price($product, 3, ['discount_id' => 1]);
        $second     = Generate::price($product, 7, ['discount_id' => 2]);
        $inactive   = Generate::price($product, 1, ['discount_id' => 3, 'start_at' => Carbon::tomorrow()]);

        $product->load('current_price');
        $this->assertEquals($product->current_price->price, $first->price);
    }

    /**
     * Ensure that discount prices are synchronized
     */
    public function test_sync_discounted_prices()
    {
        $product    = Generate::product('Foo');
        $parent     = Generate::category('Parent');
        $child      = Generate::category('Child', ['parent_id' => $parent->id]);

        // Create a discount and attach the product directly to it
        $discount = Generate::discount('Discount', ['is_percentage' => false, 'amount_exact' => 25]);
        $discount->products()->add($product);
        $product->base_price = 100;
        $product->save();
        $product->load('current_price', 'discounted_prices');
        $this->assertEquals(75, $product->current_price->price);
        $this->assertEquals(1, $product->discounted_prices->count());

        // Remove the product discount, and instead
        $discount->products()->remove($product);
        $product->changedCategories = true;
        $product->save();
        $product->load('current_price', 'discounted_prices');
        $this->assertEquals(100, $product->current_price->price);
        $this->assertEquals(0, $product->discounted_prices->count());

        // Attach a discount to parent, and make sure that product has picked it up
        $discount->categories()->add($parent);
        $product->categories()->add($child);
        $product->load('categories');
        $product->changedCategories = true;
        $product->save();
        $product->load('current_price', 'discounted_prices');
        $this->assertEquals(75, $product->current_price->price);
        $this->assertEquals(1, $product->discounted_prices->count());
    }

    /**
     * Make sure the inventory scopes work
     */
    public function test_inventory_scopes()
    {
        $first  = Generate::product('First');
        $second = Generate::product('Second');
        $inv1   = Generate::inventory($first, [], ['quantity' => 1]);
        $inv2   = Generate::inventory($second, [], ['quantity' => 0]);

        $instock = Product::inStock()->get();
        $this->assertEquals(1, $instock->count());
        $this->assertEquals(1, $instock->where('id', $first->id)->count());

        $outofstock = Product::outOfStock()->get();
        $this->assertEquals(1, $outofstock->count());
        $this->assertEquals(1, $outofstock->where('id', $second->id)->count());
    }

    /**
     * Make sure discount scopes work
     */
    public function test_discount_scopes()
    {
        $normal     = Generate::product('Normal', ['base_price' => 10]);
        $discounted = Generate::product('Discounted', ['base_price' => 10]);
        $discount   = Generate::price($discounted, 5);

        $fullprice = Product::isNotDiscounted()->with('current_price')->get();
        $this->assertEquals(1, $fullprice->count());
        $this->assertEquals(1, $fullprice->where('id', $normal->id)->count());

        $onsale = Product::isDiscounted()->get();
        $this->assertEquals(1, $onsale->count());
        $this->assertEquals(1, $onsale->where('id', $discounted->id)->count());
    }

    /**
     * Make sure that joining the price table works
     */
    public function test_join_prices_scope()
    {
        $product = Generate::product('Product', ['base_price' => 10]);
        $query = Product::joinPrices()->where('id', $product->id)->first();

        // Make sure we're joining the price, and not getting it from the relationship
        $this->assertEquals(10, $query->price);
        $this->assertFalse(isset($query->toArray()['current_price']));
    }
}
