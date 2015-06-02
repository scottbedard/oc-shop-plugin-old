<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Inventory Model
 */
class Inventory extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_inventories';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];
    public $belongsToMany = [
        'values' => [
            'Bedard\Shop\Models\Value',
            'table' => 'bedard_shop_inventory_value',
        ],
    ];

    /**
     * Compile a list of possible inventory options
     *
     * @return  array
     */
    public function getInventoryOptions()
    {
        // Create a group of value arrays
        $values = [];
        $this->load('product.options.values');
        foreach ($this->product->options as $option) {
            $values[] = $option->values->lists('name');
        }

        // Calculate the cartesian product of our values
        $options = [[]];
        foreach ($values as $key => $names) {
            $append = [];
            foreach($options as $product) {
                foreach($names as $name) {
                    $product[$key] = $name;
                    $append[] = $product;
                }
            }
            $options = $append;
        }

        // Implode our options into user-friendly strings
        $selections = [];
        $delimeter  = '<i class="delimeter icon-angle-right"></i>';
        foreach ($options as $option) {
            $selections[] = implode($delimeter, $option);
        }

        return $selections;
    }

}
