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
        $unrelated  = Generate::category('Unrelated');
        $shirt      = Generate::product('Shirt');
        $pants      = Generate::product('Pants');
        $hat        = Generate::product('Hat');
        $shoes      = Generate::product('Shoes');

        $shirt->categories()->add($parent);
        $pants->categories()->add($child);
        $hat->categories()->add($unrelated);

        $discount = Generate::discount('Shirt Pants and Shoes');
        $discount->products()->add($shoes);
        $discount->categories()->add($parent);
        $discount->load('products', 'categories');
        $discount->save();

        // There should have been prices made for $shirt, $pants, and $shoes
        $prices = Price::where('discount_id', $discount->id)->get();
        $this->assertEquals(3, $prices->count());
        $this->assertEquals(1, $prices->where('product_id', $shirt->id)->count());
        $this->assertEquals(1, $prices->where('product_id', $pants->id)->count());
        $this->assertEquals(1, $prices->where('product_id', $shoes->id)->count());

        // If we remove $child's parent_id, then $pants should no longer
        // be within the scope of this discount.
        $child->parent_id = null;
        $child->save();
        $discount->load('products', 'categories');
        $discount->save();

        $prices = Price::where('discount_id', $discount->id)->get();
        $this->assertEquals(2, $prices->count());
        $this->assertEquals(1, $prices->where('product_id', $shirt->id)->count());
        $this->assertEquals(1, $prices->where('product_id', $shoes->id)->count());
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

        $product->load('price');
        $this->assertEquals(9, $product->price->price);

        $discount->is_percentage = false;
        $discount->amount_exact = 5;
        $discount->save();

        $product->load('price');
        $this->assertEquals(5, $product->price->price);
    }
}
