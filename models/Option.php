<?php namespace Bedard\Shop\Models;

use Model;
use October\Rain\Exception\ValidationException;

/**
 * Option Model
 */
class Option extends Model
{
    use \October\Rain\Database\Traits\Validation;

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
            'order' => 'position asc',
        ],
    ];
    public $belongsTo = [
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'name' => 'required',
    ];

    /**
     * Model Events
     */
    public function afterDelete()
    {
        // todo: delete associated inventories
    }

}
