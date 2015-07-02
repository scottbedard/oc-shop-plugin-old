<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Driver Model
 */
class Driver extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_drivers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'class',
        'type',
        'is_enabled',
    ];

}
