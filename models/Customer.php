<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Customer Model
 */
class Customer extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_customers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
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
        'first_name'    => 'required',
        'last_name'     => 'required',
        'email'         => 'required|email',
    ];

}
