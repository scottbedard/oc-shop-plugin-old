<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Tests\Fixtures\Generate;

class InventoryModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * Ensure that only one "default" inventory is allowed per product
     */
    public function test_multiple_valueless_inventories_throw_exception()
    {
        $product    = Generate::product('Foo');
        $first      = Generate::inventory($product);

        $this->setExpectedException('ValidationException');
        $second = Generate::inventory($product);
    }

    /**
     * Ensure that no two inventories on the same product may have
     * the exact same values
     */
    public function test_inventory_values_must_be_unique()
    {
        $product    = Generate::product('Bar');
        $size       = Generate::option('Size');
        $small      = Generate::value($size, 'Small');
        $large      = Generate::value($size, 'Large');
        $color      = Generate::option('Color');
        $red        = Generate::value($color, 'Red');

        // These should all be valid
        $default    = Generate::inventory($product);
        $first      = Generate::inventory($product, [$small->id]);
        $second     = Generate::inventory($product, [$small->id, $red->id]);
        $third      = Generate::inventory($product, [$large->id, $red->id]);

        // This should collide with $third
        $this->setExpectedException('ValidationException');
        $fourth = Generate::inventory($product, [$large->id, $red->id]);
    }

    /**
     * Stock keeping units should be unique, and empty strings should
     * be converted to null.
     */
    public function test_sku_is_unique()
    {
        $product    = Generate::product('Baz');
        $size       = Generate::option('Size');
        $small      = Generate::value($size, 'Small');
        $large      = Generate::value($size, 'Large');

        // Duplicate empty strings should be converted to null so they don't collide
        $first = Generate::inventory($product, [], ['sku' => '']);
        $second = Generate::inventory($product, [$small->id], ['sku' => '']);

        $first->sku = 'Foo';
        $first->save();

        $this->setExpectedException('ValidationException');
        $second->sku = 'Foo';
        $second->save();

        // todo: make sku's case-insensetive
    }

    /**
     * getValueNames() should return values in the correct order
     */
    public function test_value_names_are_ordered_correctly()
    {
        $product    = Generate::product('Baz');
        $size       = Generate::option('Size', ['position' => 2]);
        $small      = Generate::value($size, 'Small');
        $color      = Generate::option('Color', ['position' => 1]);
        $red        = Generate::value($color, 'Red');

        $inventory  = Generate::inventory($product, [$small->id, $red->id]);

        $names = $inventory->getValueNames();
        $this->assertEquals($names[1], 'Red');
        $this->assertEquals($names[2], 'Small');
    }
}
