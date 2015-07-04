<?php namespace Bedard\Shop\Models;

use Model;

/**
 * ShippingRate Model
 */
class ShippingRate extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_shipping_rates';

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
        'method' => [
            'Bedard\Shop\Models\ShippingMethod',
        ],
    ];
}
