<?php namespace Bedard\Shop\Tests\Fixtures;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Product;

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
}
