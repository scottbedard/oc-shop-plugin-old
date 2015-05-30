<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Value Model
 */
class Value extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_values';

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
        'option' => [
            'Bedard\Shop\Models\Option',
        ],
    ];
    public $belongsToMany = [
        'products' => [
            'Bedard\Shop\Models\Product',
            'table' => 'bedard_shop_inventory_value',
        ],
    ];

}
