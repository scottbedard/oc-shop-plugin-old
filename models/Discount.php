<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Price;
use Bedard\Shop\Models\Product;
use DB;
use Model;
use Queue;

/**
 * Discount Model
 */
class Discount extends Model
{
    use \Bedard\Shop\Traits\NumericColumnTrait,
        \October\Rain\Database\Traits\Validation;

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_discounts';

    /**
     * @var array   Implemented behaviors
     */
    public $implement = ['Bedard.Shop.Behaviors.TimeSensitiveModel'];

    /**
     * @var array   Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array   Fillable fields
     */
    protected $fillable = [
        'is_percentage',
    ];

    /**
     * @var array   Relations
     */
    public $hasMany = [
        'prices' => [
            'Bedard\Shop\Models\Price',
        ],
    ];

    public $belongsToMany = [
        'products' => [
            'Bedard\Shop\Models\Product',
            'table'         => 'bedard_shop_discount_product',
        ],
        'categories' => [
            'Bedard\Shop\Models\Category',
            'table'         => 'bedard_shop_discount_category',
            'scope'         => 'isNotFiltered',
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
        'end_at.after'      => 'bedard.shop::lang.common.end_at_invalid',
    ];

    /**
     * Model Events
     */
    public function afterSave()
    {
        // Synchronize product prices
        $this->syncProducts();
    }

    public function afterDelete()
    {
        // Delete discount prices
        foreach ($this->prices as $price) {
            $price->delete();
        }
    }

    /**
     * Query Scopes
     */
    public function scopeSelectAmount($query)
    {
        return $query->addSelect($this->selectNumeric(
            $this->table,
            'is_percentage',
            'amount_percentage',
            'amount_exact'
        ));
    }

    /**
     * Accessors and Mutators
     */
    public function setAmountExactAttribute($value)
    {
        $this->attributes['amount_exact'] = $value ?: 0;
    }

    public function setAmountPercentageAttribute($value)
    {
        $this->attributes['amount_percentage'] = $value ?: 0;
    }

    /**
     * Filter form fields
     */
    public function filterFields($fields, $context = null)
    {
        $fields->amount_exact->hidden = $this->is_percentage;
        $fields->amount_percentage->hidden = !$this->is_percentage;
    }

    /**
     * Synchronizes a discount with the products effected by it
     */
    public function syncProducts()
    {
        // First, grab all products within the scope of this discount
        $products = Product::whereIn('id', $this->products->lists('id'))
            ->orWhereHas('categories', function($category) {
                $category->where(function($query) {
                    $query
                        ->whereIn('id', $this->categories->lists('id'))
                        ->orWhereHas('inherited_by', function($inherited_by) {
                            $inherited_by->whereIn('parent_id', $this->categories->lists('id'));
                        });
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

    /**
     * Synchronize the products of all active or upcoming discounts
     */
    public static function syncAllProducts()
    {
        $discounts = self::isRunningOrUpcoming()->get();
        foreach ($discounts as $discount) {
            $id = $discount->id;
            Queue::push(function($job) use($id) {
                $target = Discount::find($id);
                $target->syncProducts();
                $job->delete();
            });
        }
    }
}
