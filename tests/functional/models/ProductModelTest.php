<?php namespace Bedard\Shop\Tests\Functional\Models;

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
        $product = Product::create(['name' => 'Foo', 'slug' => 'foo', 'base_price' => '']);
        $this->assertNotNull($product->id);
        $this->assertEquals($product->base_price, 0);
    }

    /**
     * Markdown descriptions should be cached and stored in the
     * description_html column
     */
    public function test_caching_description_markdown()
    {
        $product = Product::create(['name' => 'Bar', 'slug' => 'bar', 'description' => '# Hello']);
        $this->assertEquals('<h1>Hello</h1>', $product->description_html);
    }

    /**
     * Pivot tables should be kept clean when a product is deleted
     */
    public function test_pivot_tables_are_kept_clean()
    {
        $category   = Category::create(['name' => 'Category', 'slug' => 'category']);
        $product    = Product::create(['name' => 'Product', 'slug' => 'product']);

        $product->categories()->add($category);
        $product->load('categories');
        $product->syncCategories();
        $this->assertNotNull(DB::table('bedard_shop_cat_prod')->where('product_id', $product->id)->first());
        $this->assertNotNull(DB::table('bedard_shop_cat_prod_display')->where('product_id', $product->id)->first());

        $product->delete();
        $this->assertNull(DB::table('bedard_shop_cat_prod')->where('product_id', $product->id)->first());
        $this->assertNull(DB::table('bedard_shop_cat_prod_display')->where('product_id', $product->id)->first());
    }

    /**
     * Products should synchronize the ,ir display categories to reflect parent
     * categories that are inheriting their children.
     */
    public function test_synchronize_display_categories()
    {
        $parent     = Category::create(['name' => 'Parent', 'slug' => 'parent', 'is_inheriting' => false]);
        $child      = Category::create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id, 'is_inheriting' => true]);
        $grandchild = Category::create(['name' => 'Grandchild', 'slug' => 'grandchild', 'parent_id' => $child->id, 'is_inheriting' => true]);

        $product = Product::create(['name' => 'Product', 'slug' => 'product']);
        $product->categories()->sync([$grandchild->id]);

        $product->load('categories', 'displayCategories');
        $product->save();
        $product->load('displayCategories');

        $this->assertNull($product->displayCategories->find($parent->id));
        $this->assertNotNull($product->displayCategories->find($child->id));
        $this->assertNotNull($product->displayCategories->has($grandchild->id));
    }

    /**
     * Tests the ability to synchronize multiple products at once. This is used
     * from bulk-edit tasks like re-ordering the category tree.
     */
    public function test_synchronizing_multiple_products()
    {
        $parent = Category::create(['name' => 'Parent', 'slug' => 'parent', 'is_inheriting' => false]);
        $child  = Category::create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id]);
        $hat    = Product::create(['name' => 'Hat', 'slug' => 'hat']);
        $shirt  = Product::create(['name' => 'Shirt', 'slug' => 'shirt']);
        $hat->categories()->sync([$parent->id, $child->id]);
        $shirt->categories()->sync([$child->id]);

        $this->assertEquals(0, count($hat->displayCategories));
        $this->assertEquals(0, count($shirt->displayCategories));

        Product::syncProducts(Product::all());

        $hat->load('displayCategories');
        $shirt->load('displayCategories');
        $this->assertEquals(2, count($hat->displayCategories));
        $this->assertEquals(1, count($shirt->displayCategories));
    }

    /**
     * Makes sure that the isActive() and isNotActive() scopes work
     */
    public function test_isActive_and_isNotActive_scopes()
    {
        $active     = Product::create(['name' => 'Active', 'slug' => 'active', 'is_active' => true]);
        $disabled   = Product::create(['name' => 'Disabled', 'slug' => 'filtered', 'is_active' => false]);

        $isActive = Product::isActive()->first();
        $this->assertEquals($active->id, $isActive->id);

        $isNotActive = Product::isNotActive()->first();
        $this->assertEquals($disabled->id, $isNotActive->id);
    }
}
