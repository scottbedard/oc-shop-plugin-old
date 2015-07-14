<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Value Model
 */
class Value extends Model
{

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_values';

    /**
     * @var array   Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array   Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array   Cacheable fields
     */
    public $cacheable = [
        'id',
        'option_id',
        'name',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'option' => [
            'Bedard\Shop\Models\Option',
        ],
    ];

    public $belongsToMany = [
        'inventories' => [
            'Bedard\Shop\Models\Inventory',
            'table' => 'bedard_shop_inventory_value',
        ],
        'products' => [
            'Bedard\Shop\Models\Product',
            'table' => 'bedard_shop_inventory_value',
        ],
    ];

    /**
     * Model Events
     */
    public function afterDelete()
    {
        foreach ($this->inventories as $inventory) {
            $inventory->delete();
        }
    }
}
