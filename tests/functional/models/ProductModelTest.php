<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Tests\Fixtures\Generate;
use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Product;
use DB;

class ProductModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * Some databases won't accept an empty string as a decimal
     * column, even if a default is specified. This makes sure
     * that a zero value is inserted.
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
}
