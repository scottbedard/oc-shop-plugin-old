<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Tests\Fixtures\Generate;

class ValueModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_inventories_are_deleted()
    {
        $product    = Generate::product('Foo');
        $option     = Generate::option('Size', ['product_id' => $product->id]);
        $value      = Generate::value($option, 'Small');

        $inventory = Generate::inventory($product, [$value->id]);

        $inventories = Inventory::where('product_id', $product->id)
            ->whereHas('values', function($query) use ($value) {
                $query->where('id', $value->id);
            })
            ->get();
        $this->assertEquals(1, $inventories->count());

        $value->delete();
        $inventories = Inventory::where('product_id', $product->id)
            ->whereHas('values', function($query) use ($value) {
                $query->where('id', $value->id);
            })
            ->get();
        $this->assertEquals(0, $inventories->count());
    }
}
