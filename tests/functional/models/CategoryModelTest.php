<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Tests\Fixtures\Generate;
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
        $parent = Generate::category('Parent');
        $child = Generate::category('Child', ['parent_id' => $parent->id]);

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
        $first  = Generate::category('First');
        $second = Generate::category('Second');
        $third  = Generate::category('Third', ['parent_id' => $second->id]);

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
        $normal     = Generate::category('Normal');
        $filtered   = Generate::category('Filtered', ['filter' => 'all']);

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
        $category   = Generate::category('Category');
        $product    = Generate::product('Product');

        $product->categories()->add($category);
        $this->assertNotNull(
            DB::table('bedard_shop_category_product')
                ->where('category_id', $category->id)
                ->first()
        );

        $category->delete();
        $this->assertNull(
            DB::table('bedard_shop_category_product')
                ->where('category_id', $category->id)
                ->first()
        );
    }
}
