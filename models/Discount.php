<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Price;
use DB;
use Illuminate\Database\Eloquent\Collection;
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
        // Clear the current prices so they can be re-calculated
        $this->prices()->delete();

        // Determine the scope of this discount. It should apply to all selected
        // products, and the products of any categories or inherited categories.
        $scope = $this->products;
        $this->categories->load('products', 'inherited.products');
        foreach ($this->categories as $category) {
            $scope = $scope->merge($category->products);

            foreach ($category->inherited as $inherited) {
                $scope = $scope->merge($inherited->products);
            }
        }

        // Create a price model for each product
        foreach ($scope->unique() as $product) {
            $discount = $this->is_percentage
                ? $product->base_price * ($this->amount_percentage / 100)
                : $this->amount_exact;

            Price::create([
                'product_id'    => $product->id,
                'discount_id'   => $this->id,
                'price'         => $product->base_price - $discount,
                'start_at'      => $this->start_at,
                'end_at'        => $this->end_at,
            ]);
        }
    }

}
