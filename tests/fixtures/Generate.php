<?php namespace Bedard\Shop\Tests\Fixtures;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Option;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Value;

class Generate {

    /**
     * Creates a category for use in tests
     *
     * @param   string      $name
     * @param   array       $data
     * @return  Category
     */
    public static function category($name, $data = [])
    {
        $category = new Category;
        $category->name = $name;
        $category->slug = str_replace(' ', '-', strtolower($name));

        foreach ($data as $key => $value) {
            $category->$key = $value;
        }

        $category->save();
        return $category;
    }

    /**
     * Generate an inventory for use in tests
     *
     * @param   Product     $product
     * @param   array       $values
     * @param   array       $data
     */
    public static function inventory(Product $product, $values = [], $data = [])
    {
        $inventory = new Inventory;
        $inventory->product_id = $product->id;

        foreach ($data as $key => $value) {
            $inventory->$key = $value;
        }

        $inventory->saveWithValues($values);
        return $inventory;
    }

    /**
     * Generates an option for use in tests
     *
     * @param   string      $name
     * @param   array       $data
     * @return  Option
     */
    public static function option($name, $data = [])
    {
        $option = new Option;
        $option->name = $name;

        foreach ($data as $key => $value) {
            $option->$key = $value;
        }

        $option->save();
        return $option;
    }

    /**
     * Generate a product for use in tests
     *
     * @param   string      $name
     * @param   array       $data
     * @return  Product
     */
    public static function product($name, $data = [])
    {
        $product = new Product;
        $product->name = $name;
        $product->slug = str_replace(' ', '-', strtolower($name));

        foreach ($data as $key => $value) {
            $product->$key = $value;
        }

        $product->save();
        return $product;
    }

    /**
     * Generate a value for use in tests
     *
     * @param   Option      $option
     * @param   string      $name
     * @param   array       $data
     * @return  Value
     */
    public static function value(Option $option, $name, $data = [])
    {
        $value = new Value;
        $value->option_id = $option->id;
        $value->name = $name;

        foreach ($data as $key => $v) {
            $value->$key = $v;
        }

        $value->save();
        return $value;
    }
}
