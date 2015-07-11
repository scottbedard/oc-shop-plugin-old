<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Status Model
 */
class Status extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_statuses';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'icon',
        'class',
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'events' => [
            'Bedard\Shop\Models\OrderEvent',
        ],
        'orders' => [
            'Bedard\Shop\Models\Order',
        ],
    ];

}
