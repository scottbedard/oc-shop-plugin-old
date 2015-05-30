<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Product;
use DB;

class CategoryModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * When a parent category is deleted, it's child categories should
     * should be updated to have a NULL parent_id.
     */
    public function test_orphaned_categories_have_null_parent_id()
    {
        $parent = Category::create(['name' => 'Parent', 'slug' => 'parent']);
        $child  = Category::create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id]);

        $this->assertEquals($parent->id, $child->parent_id);
        $parent->delete();

        $child = Category::find($child->id);
        $this->assertNull($child->parent_id);
    }

    /**
     * Categories should not be nestable under it's own children
     */
    public function test_nesting_loop_throws_validation_exception()
    {
        $first  = Category::create(['name' => 'First', 'slug' => 'first']);
        $second = Category::create(['name' => 'Second', 'slug' => 'second']);
        $third  = Category::create(['name' => 'Third', 'slug' => 'third', 'parent_id' => $second->id]);

        // This nesting is fine
        $second->parent_id = $first->id;
        $second->save();

        // This nesting should throw an exception
        $this->setExpectedException('ValidationException');
        $first->parent_id = $third->id;
        $first->save();
    }

    /**
     * Makes sure that the isFiltered() and isNotFiltered() scopes work
     */
    public function test_isFiltered_and_isNotFiltered_scopes()
    {
        $normal     = Category::create(['name' => 'Normal', 'slug' => 'normal']);
        $filtered   = Category::create(['name' => 'Filtered', 'slug' => 'filtered', 'filter' => 'all']);

        $first = Category::isNotFiltered()->first();
        $this->assertEquals($normal->id, $first->id);

        $second = Category::isFiltered()->first();
        $this->assertEquals($filtered->id, $second->id);
    }

    /**
     * Pivot tables should be kept clean when a category is deleted
     */
    public function test_pivot_tables_are_kept_clean()
    {
        $category   = Category::create(['name' => 'Category', 'slug' => 'category']);
        $product    = Product::create(['name' => 'Product', 'slug' => 'product']);

        $product->categories()->add($category);
        $this->assertNotNull(
            DB::table('bedard_shop_cat_prod')
                ->where('category_id', $category->id)
                ->first()
        );

        $category->delete();
        $this->assertNull(
            DB::table('bedard_shop_cat_prod')
                ->where('category_id', $category->id)
                ->first()
        );
    }

    /**
     * Make sure products are synchronizing their display categories
     * when a category is updated.
     */
    public function test_products_are_synchronized_after_updates()
    {
        $one    = Category::create(['name' => 'One', 'slug' => 'one', 'is_inheriting' => false]);
        $two    = Category::create(['name' => 'Two', 'slug' => 'two', 'parent_id' => $one->id, 'is_inheriting' => true]);
        $three  = Category::create(['name' => 'Three', 'slug' => 'three', 'parent_id' => $two->id]);

        $product = Product::create(['name' => 'Product', 'slug' => 'product']);
        $product->categories()->sync([$three->id]);

        $one->load('displayProducts');
        $this->assertEquals(0, count($one->displayProducts));

        $one->is_inheriting = true;
        $one->save();

        $one->load('displayProducts');
        $this->assertEquals(1, count($one->displayProducts));

        $two->parent_id = null;
        $two->save();

        $one->load('displayProducts');
        $this->assertEquals(0, count($one->displayProducts));
    }

    /**
     * If a link in the chain is broken, categories should not inherit
     * products from "ghost" categories.
     */
    public function test_products_are_synchronized_after_deletes()
    {
        $one    = Category::create(['name' => 'One', 'slug' => 'one', 'is_inheriting' => true]);
        $two    = Category::create(['name' => 'Two', 'slug' => 'two', 'parent_id' => $one->id, 'is_inheriting' => true]);
        $three  = Category::create(['name' => 'Three', 'slug' => 'three', 'parent_id' => $two->id]);

        $product = Product::create(['name' => 'Product', 'slug' => 'product']);
        $product->categories()->sync([$three->id]);

        Product::syncProducts(Product::all());

        $one->load('displayProducts');
        $this->assertEquals(1, count($one->displayProducts));

        $two->delete();
        $one->load('displayProducts');
        $this->assertEquals(0, count($one->displayProducts));
    }
}
