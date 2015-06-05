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
     * Ensure that prices are updated after saving
     */
    public function test_price_models_are_updated()
    {
       $product     = Generate::product('Stuff', ['base_price' => 100]);
       $discount    = Generate::discount('Cheap stuff', ['is_percentage' => false, 'amount_exact' => 25]);
       $discount->products()->add($product);
       $discount->load('products');
       $discount->save();

       // Verify that the correct price models exist to start with
       $prices = Price::where('product_id', $product->id)->get();
       $this->assertEquals(2, $prices->count());
       $base = $prices->where('product_id', $product->id)->where('discount_id', null)->first();
       $discounted = $prices->where('product_id', $product->id)->where('discount_id', $discount->id)->first();
       $this->assertEquals(100, $product->base_price);
       $this->assertEquals(75, $discounted->price);

       // Change the product price
       $product->base_price = 200;
       $product->save();

       // Verify that the price models have been updated
       $prices = Price::where('product_id', $product->id)->get();
       $this->assertEquals(2, $prices->count());
       $base = $prices->where('product_id', $product->id)->where('discount_id', null)->first();
       $discounted = $prices->where('product_id', $product->id)->where('discount_id', $discount->id)->first();
       $this->assertEquals(200, $product->base_price);
       $this->assertEquals(175, $discounted->price);
    }
}
