<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Price;
use Bedard\Shop\Models\Product;
use DB;
use Model;

/**
 * Discount Model
 */
class Discount extends Model
{
    use \Bedard\Shop\Traits\DateActiveTrait,
        \October\Rain\Database\Traits\Validation;

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
    public $hasMany = [
        'prices' => [
            'Bedard\Shop\Models\Price',
        ],
    ];

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
        'start_at'          => 'date',
        'end_at'            => 'date',
    ];

    public $customMessages = [
        'end_at.after'      => 'bedard.shop::lang.discounts.end_at_invalid',
    ];

    /**
     * Model Events
     */
    public function beforeValidate()
    {
        if ($this->start_at && $this->end_at) {
            $this->rules['end_at'] = 'after:start_at';
        }
    }

    public function afterSave()
    {
        // Synchronize product prices
        $this->syncProducts();
    }

    public function filterFields($fields, $context = null)
    {
        $fields->amount_exact->hidden = $this->is_percentage;
        $fields->amount_percentage->hidden = !$this->is_percentage;
    }

    public function setAmountExactAttribute($value)
    {
        $this->attributes['amount_exact'] = $value ?: 0;
    }

    public function setAmountPercentageAttribute($value)
    {
        $this->attributes['amount_percentage'] = $value ?: 0;
    }

    /**
     * Synchronizes a discount with the products effected by it
     */
    public function syncProducts()
    {
        // First, grab all products within the scope of this discount
        $products = Product::whereIn('id', $this->products->lists('id'))
            ->orWhereHas('categories', function($categories) {
                $ids = $this->categories->lists('id');
                $categories->whereIn('id', $ids)
                    ->orWhereHas('inherited', function($inherited) use ($ids) {
                        $inherited->whereIn('parent_id', $ids);
                    });
            })
            ->get();

        // Next, reset all discounted price models
        $this->prices()->delete();
        foreach ($products as $product) {
            Price::create([
                'product_id'    => $product->id,
                'discount_id'   => $this->id,
                'price'         => $this->calculate($product->base_price),
                'start_at'      => $this->start_at,
                'end_at'        => $this->end_at
            ]);
        }
    }

    /**
     * Calculates a discounted price
     *
     * @param   float   $base_price
     * @return  float
     */
    public function calculate($base_price)
    {
        $discount = $this->is_percentage
            ? $base_price * ($this->amount_percentage / 100)
            : $this->amount_exact;

        $price = round($base_price - $discount, 2);
        return $price > 0 ? $price : 0;
    }

}
