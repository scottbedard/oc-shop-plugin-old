<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Option Model
 */
class Option extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_options';

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
    public $hasMany = [
        'values' => [
            'Bedard\Shop\Models\Value',
        ],
    ];
    public $belongsTo = [
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];

    /**
     * Model Events
     */
    public function afterDelete()
    {
        // todo: delete associated inventories
    }

}
