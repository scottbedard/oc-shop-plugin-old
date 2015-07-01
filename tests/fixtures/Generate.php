<?php namespace Bedard\Shop\Tests\Fixtures;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Discount;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Option;
use Bedard\Shop\Models\Price;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Promotion;
use Bedard\Shop\Models\Value;

class Generate {

    /**
     * Create a cart for use in tests
     *
     * @return  Cart
     */
    public static function cart()
    {
        return Cart::create(['key' => str_random(40)]);
    }

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
     * Create a discount for use in tests
     *
     * @param   string  $name
     * @param   array   $data
     * @return  Discount
     */
    public static function discount($name, $data = [])
    {
        $discount = new Discount;
        $discount->name = $name;

        foreach ($data as $key => $value) {
            $discount->$key = $value;
        }

        $discount->save();
        return $discount;
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
     * Generates an price for use in tests
     *
     * @param   Product     $product
     * @param   float       $price
     * @param   array       $data
     * @return  Price
     */
    public static function price(Product $product, $price, $data = [])
    {
        $model = new Price;
        $model->product_id = $product->id;
        $model->price = $price;

        foreach ($data as $key => $value) {
            $model->$key = $value;
        }

        $model->save();
        return $model;
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
        $product->load('current_price');
        return $product;
    }

    /**
     * Generate a promotion for use in tests
     *
     * @param   string      $code
     * @param   array       $data
     * @return  Promotion
     */
    public static function promotion($code, $data = [])
    {
        $promotion = new Promotion;
        $promotion->code = $code;

        foreach ($data as $key => $value) {
            $promotion->$key = $value;
        }

        $promotion->save();
        return $promotion;
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
