<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Address Model
 */
class Address extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_addresses';

    /**
     * @var array   Implemented behaviors
     */
    public $implement = ['RainLab.Location.Behaviors.LocationModel'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'organization',
        'street_1',
        'street_2',
        'city',
        'postal_code',
        'state_name',
        'is_billing',
        'is_shipping',
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'carts' => [
            'Bedard\Shop\Models\Cart',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'steet_1' => 'required',
    ];

}
