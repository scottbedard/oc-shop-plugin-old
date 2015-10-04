<?php namespace Bedard\Shop\Classes;

use Bedard\Shop\Models\Cart;
use Bedard\Shop\Models\CartItem;
use Bedard\Shop\Models\Inventory;
use Bedard\Shop\Models\Option;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Promotion;
use Bedard\Shop\Models\Value;
use Illuminate\Database\Eloquent\Collection;

class CartCache {
    // todo: write tests

    /**
     * Build a Cart collection from cached data
     *
     * @param   array       $data
     * @return  Cart
     */
    public function build($data)
    {
        $cart = $this->fillModel((new Cart), $data);
        $cart->setRelation('items', $this->buildCartItems($data));
        $cart->setRelation('promotion', $this->buildPromotion($data));

        return $cart;
    }

    /**
     * Loads cache data and returns the array value of a cart
     *
     * @param   Cart        $cart
     * @return  array
     */
    public function cache(Cart $cart)
    {
        $cart->load([
            'items' => function($item) {
                $item->withTrashed()->with([
                    'inventory' => function($inventory) {
                        $inventory->selectCacheable()->with([
                            'product' => function($product) {
                                $product->joinPrices()->selectCacheable();
                            },
                            'values' => function($values) {
                                $values->selectCacheable()->with([
                                    'option' => function($option) {
                                        $option->selectCacheable();
                                    }
                                ]);
                            }
                        ]);
                    }
                ]);
            },
            'promotion' => function($promotion) {
                $promotion->selectCacheable()->with([
                    'products' => function($product) {
                        $product->addSelect('id');
                    }
                ]);
            },
        ]);

        return $cart->toArray();
    }

    /**
     * Build up a collection of CartItem models
     *
     * @param   array       $data
     * @return  Collection
     */
    protected function buildCartItems($data)
    {
        $items = new Collection;

        if (array_key_exists('items', $data)) {
            foreach ($data['items'] as $item) {
                $model = $this->fillModel((new CartItem), $item);
                $model->setRelation('inventory', $this->buildInventory($item));
                $items->push($model);
            }
        }

        return $items;
    }

    /**
     * Build an Inventory model from cached data
     *
     * @param   array       $data
     * @return  Inventory
     */
    protected function buildInventory($data)
    {
        $inventory = new Inventory;

        if (array_key_exists('inventory', $data)) {
            $inventory = $this->fillModel($inventory, $data['inventory']);
            $inventory->setRelation('product', $this->buildProduct($data['inventory']));
            $inventory->setRelation('values', $this->buildValues($data['inventory']));
        }

        return $inventory;
    }

    /**
     * Build a Product model from cached data
     *
     * @param   array       $data
     * @return  Product
     */
    protected function buildProduct($data)
    {
        return array_key_exists('product', $data)
            ? $this->fillModel((new Product), $data['product'])
            : new Product;
    }

    /**
     * Build a Values collection from cached data
     *
     * @param   array       $data
     * @return  Collection
     */
    protected function buildValues($data)
    {
        $values = new Collection;

        if (array_key_exists('values', $data)) {
            foreach ($data['values'] as $value) {
                $model = $this->fillModel((new Value), $value);
                $model->setRelation('option', $this->buildOption($value));
                $values->push($model);
            }
        }

        return $values;
    }

    /**
     * Build an Option model from cached data
     *
     * @param   array       $data
     * @return  Option
     */
    protected function buildOption($data)
    {
        return array_key_exists('option', $data)
            ? $this->fillModel((new Option), $data['option'])
            : new Option;
    }

    /**
     * Builds the Promotion model from cached data
     *
     * @param   array       $data
     * @return  Promotion
     */
    protected function buildPromotion($data)
    {
        return array_key_exists('promotion', $data) && !is_null($data['promotion'])
            ? $this->fillModel((new Promotion), $data['promotion'])
            : null;
    }

    /**
     * Fill a model using it's cacheable fields
     *
     * @param   Model   $model
     * @param   array   $data
     * @return  Model
     */
    protected function fillModel($model, $data = [])
    {
        if (is_array($model->cacheable) && $data) {
            foreach ($model->cacheable as $property) {
                if (array_key_exists($property, $data)) {
                    $array = $this->isJson($data[$property]);
                    $model->$property = $array ?: $data[$property];
                }
            }
        }

        return $model;
    }

    /**
     * Checks if a string is json, and if so returns the decoded array
     *
     * @param   string      $string
     * @return  false|array
     */
    protected function isJson($string)
    {
        if (is_null($string) || strlen($string) < 1 || $string[0] != '{' && $string[0] != '[') {
            return false;
        }

        $array = json_decode($string, true);

        return (json_last_error() == JSON_ERROR_NONE)
            ? $array
            : false;
    }
}
