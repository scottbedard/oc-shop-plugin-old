<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Price;
use Bedard\Shop\Tests\Fixtures\Generate;

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

    public function test_calculate_price()
    {
        $percentage = Generate::discount('Foo', ['is_percentage' => true, 'amount_percentage' => 25]);
        $this->assertEquals(75, $percentage->calculate(100)); // 100 - 25% = 75

        $exact = Generate::discount('Foo', ['is_percentage' => false, 'amount_exact' => 35]);
        $this->assertEquals(65, $exact->calculate(100)); // 100 - 35 = 65
    }
}
