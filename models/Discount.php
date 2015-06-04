<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Discount Model
 */
class Discount extends Model
{
    use \October\Rain\Database\Traits\Validation;

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

    /**
     * Validation
     */
    public $rules = [
        'name'              => 'required',
        'amount_exact'      => 'numeric|min:0',
        'amount_percentage' => 'integer|min:0|max:100',
    ];

    public $customMessages = [
        'end_at.after'      => 'bedard.shop::lang.discounts.end_at_invalid',
    ];

    public function beforeValidate()
    {
        if ($this->start_at && $this->end_at) {
            $this->rules['end_at'] = 'after:start_at';
        }
    }

    public function filterFields($fields, $context = null)
    {
        $fields->amount_exact->hidden = $this->is_percentage;
        $fields->amount_percentage->hidden = !$this->is_percentage;
    }

}
