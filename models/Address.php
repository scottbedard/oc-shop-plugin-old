<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Address Model
 */
class Address extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_addresses';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'recipient',
        'organization',
        'street_1',
        'street_2',
        'city',
        'postal_code',
        'state_name',
        'state_id',
        'country_id',
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

}
