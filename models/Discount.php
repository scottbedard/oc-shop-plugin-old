<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Discount Model
 */
class Discount extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_discounts';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'is_percentage',
    ];

    /**
     * @var array Relations
     */
    public $morphedByMany = [
        'products' => [
            'Bedard\Shop\Models\Product',
            'table'         => 'bedard_shop_discountables',
            'name'          => 'discountable',
            'foreignKey'    => 'discountable_id',
        ],
        'categories' => [
            'Bedard\Shop\Models\Category',
            'table'         => 'bedard_shop_discountables',
            'name'          => 'discountable',
            'foreignKey'    => 'discountable_id',
        ]
    ];

    public function filterFields($fields, $context = null)
    {
        $fields->amount_exact->hidden = $this->is_percentage;
        $fields->amount_percentage->hidden = !$this->is_percentage;
    }

}
