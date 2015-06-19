<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Category;
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

    public function test_category_inheritance()
    {
        $parent     = Generate::category('Parent', ['is_inheriting' => true]);
        $child      = Generate::category('Child', ['parent_id' => $parent->id, 'is_inheriting' => true]);
        $grandchild = Generate::category('Grandchild', ['parent_id' => $child->id, 'is_inheriting' => true]);
        $unrelated  = Generate::category('Unrelated');

        // Parent should be inheriting Child and Grandchild
        $parent->load('inherited');
        $this->assertTrue((bool) $parent->inherited->find($child->id));
        $this->assertTrue((bool) $parent->inherited->find($grandchild->id));
        $this->assertFalse((bool) $parent->inherited->find($unrelated->id));

        // Change Child's inheritance, and Parent should no longer have Grandchild
        $child->is_inheriting = false;
        $child->changedNesting = true;
        $child->save();
        $parent->load('inherited');
        $this->assertTrue((bool) $parent->inherited->find($child->id));
        $this->assertFalse((bool) $parent->inherited->find($grandchild->id));

        // If we delete child, Parent should no longer be inheriting anything
        $child->delete();
        $parent->load('inherited');
        $this->assertEquals($parent->inherited->count(), 0);
    }

    public function test_category_nesting_changes_effect_discount_scope()
    {
        $parent     = Generate::category('Parent', ['is_inheriting' => true]);
        $child      = Generate::category('Child', ['parent_id' => $parent->id, 'is_inheriting' => true]);
        $grandchild = Generate::category('Grandchild', ['parent_id' => $child->id, 'is_inheriting' => true]);

        $product = Generate::product('Product', ['base_price' => 100]);
        $product->categories()->add($grandchild);

        $discount = Generate::discount('Discount', ['is_percentage' => false, 'amount_exact' => 25]);
        $discount->categories()->add($parent);
        $discount->load('categories');
        $discount->save();

        // The product should be discounted because of Parent's inheritance of Grandchild
        $product->load('current_price');
        $this->assertEquals(75, $product->current_price->price);

        // Break the link from Parent to Grandchild
        $child->is_inheriting = false;
        $child->changedNesting = true;
        $child->save();

        // The product should no longer be discounted because the inheritance was broken
        $product->load('current_price');
        $this->assertEquals(100, $product->current_price->price);
    }

    public function test_sort_accessor_and_mutator()
    {
        $category = Generate::category('Foo', ['sort' => 'price-asc']);
        $this->assertEquals('price-asc', $category->sort);
        $this->assertEquals('price', $category->sort_key);
        $this->assertEquals('asc', $category->sort_order);
    }

    public function test_get_products_and_count_products()
    {
        $parent = Generate::category('Category', [
            'rows'          => 1,
            'columns'       => 2,
            'is_inheriting' => true,
        ]);

        $child          = Generate::category('Child', ['parent_id' => $parent->id, 'is_inheriting' => true]);
        $grandchild     = Generate::category('Grandchild', ['parent_id' => $child->id, 'is_inheriting' => false]);
        $uninherited    = Generate::category('Uninherited', ['parent_id' => $grandchild->id]);
        $unrelated      = Generate::category('Unrelated');

        // Create some dummy products and add them to the category
        $one    = Generate::product('One', ['base_price' => 1]);
        $two    = Generate::product('Two', ['base_price' => 2]);
        $three  = Generate::product('Three', ['base_price' => 3]);
        $four   = Generate::product('Four', ['base_price' => 4]);
        $five   = Generate::product('Five', ['base_price' => 5]);
        $one->categories()->add($parent);
        $two->categories()->add($child);
        $three->categories()->add($grandchild);
        $four->categories()->add($unrelated);
        $five->categories()->add($uninherited);
        $price = Generate::price($five, 4.5);

        // Make sure we are counting the products correctly
        $this->assertEquals(3, $parent->countProducts());

        // Get the products ordered by price
        $parent->sort_key     = 'price';
        $parent->sort_order   = 'asc';
        $page = $parent->getProducts();
        $this->assertEquals(2, $page->count());
        $this->assertEquals($one->id, $page[0]->id);
        $this->assertEquals($two->id, $page[1]->id);
        $parent->sort_order   = 'desc';
        $page = $parent->getProducts();
        $this->assertEquals($three->id, $page[0]->id);
        $this->assertEquals($two->id, $page[1]->id);

        // Test the filters
        $parent->filter = 'all';
        $this->assertEquals(5, $parent->countProducts());

        $parent->filter = 'discounted';
        $this->assertEquals(1, $parent->countProducts());
        $this->assertEquals($five->id, $parent->getProducts()->first()->id);

        $parent->filter = 'price_less';
        $parent->filter_value = 3;
        $this->assertEquals(2, $parent->countProducts());
        $products = $parent->getProducts(false);
        $this->assertTrue((bool) $products->where('id', $one->id)->first());
        $this->assertTrue((bool) $products->where('id', $two->id)->first());
        $this->assertFalse((bool) $products->where('id', $three->id)->first());
    }
}
