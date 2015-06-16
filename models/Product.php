<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Discount;
use Bedard\Shop\Models\Price;
use DB;
use Lang;
use Markdown;
use Model;

/**
 * Product Model
 */
class Product extends Model
{
    use \October\Rain\Database\Traits\Purgeable,
        \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_products';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'slug',
        'base_price',
        'description',
        'description_html',
        'is_active',
    ];

    /**
     * @var array Purgeable fields
     */
    protected $purgeable = ['optionsinventories'];

    /**
     * @var array Relations
     */
    public $attachMany = [
        'images' => [
            'System\Models\File',
            'order' => 'sort_order'
        ],
        'thumbnails' => [
            'System\Models\File',
            'order' => 'sort_order'
        ],
    ];

    public $belongsToMany = [
        'categories' => [
            'Bedard\Shop\Models\Category',
            'table' => 'bedard_shop_category_product',
        ],
    ];

    public $hasMany = [
        'inventories' => [
            'Bedard\Shop\Models\Inventory',
        ],
        'options' => [
            'Bedard\Shop\Models\Option',
            'order' => 'position asc',
        ],
        'discounted_prices' => [
            'Bedard\Shop\Models\Price',
            'scope' => 'isDiscounted',
        ],
    ];

    public $hasOne = [
        'current_price' => [
            'Bedard\Shop\Models\Price',
            'scope' => 'isActive',
            'order' => 'price asc',
        ],
    ];

    public $morphToMany = [
        'discounts' => [
            'Bedard\Shop\Models\Discount',
            'table'         => 'bedard_shop_discountables',
            'name'          => 'discountable',
            'foreignKey'    => 'discount_id',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'name'          => 'required',
        'slug'          => 'required|between:3,64|unique:bedard_shop_products',
        'base_price'    => 'numeric|min:0',
    ];

    public $customMessages = [
        'base_price.min'        => 'bedard.shop::lang.products.base_price_min',
        'base_price.numeric'    => 'bedard.shop::lang.products.base_price_numeric',
    ];

    /**
     * @var boolean     These help determine when prices need to be calculated
     */
    public $changedCategories = false;
    public $changedPrice = false;

    /**
     * Model Events
     */
    public function afterCreate()
    {
        $this->syncBasePrice();
    }

    public function beforeSave()
    {
        // This exists to help sqlite handle a null base_price
        $this->attributes['base_price'] = $this->base_price ?: 0;
    }

    public function afterSave()
    {
        // Sync Price models if the base_price or categories have changed
        if ($this->changedPrice) {
            $this->syncBasePrice();
        }

        if ($this->changedPrice || $this->changedCategories) {
            $this->syncDiscountedPrices();
        }
    }

    public function afterDelete()
    {
        // Keep pivot tables clean
        DB::table($this->belongsToMany['categories']['table'])
            ->where('product_id', $this->attributes['id'])
            ->delete();

        // Clean up prices
        Price::where('product_id', $this->id)->delete();
    }

    /**
     * Query Scopes
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('inventories', function($inventory) {
            $inventory->where('quantity', '>', 0);
        });
    }

    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIsNotActive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeFilterByCategory($query, $filter)
    {
        return $query->whereHas('categories', function($category) use ($filter) {
            $category->whereIn('id', $filter);
        });
    }

    public function scopeOutOfStock($query)
    {
        $query->whereDoesntHave('inventories', function($inventory) {
            $inventory->where('quantity', '>', 0);
        });
    }

    /**
     * Returns a list of all non-filtered categories
     *
     * @return  array
     */
    public function getCategoriesOptions()
    {
        return Category::isNotFiltered()->lists('name', 'id');
    }

    public function setBasePriceAttribute($value)
    {
        // Keep track of when the price changes
        if (!isset($this->attributes['base_price']) || $value != $this->attributes['base_price']) {
            $this->changedPrice = true;
        }

        $this->attributes['base_price'] = $value > 0 ? $value : 0;
    }

    /**
     * Cache the compiled markdown description
     *
     * @param   string  $value
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = $value;
        $this->attributes['description_html'] = Markdown::parse(trim($value));
    }

    /**
     * Determines if a product is discounted or not
     *
     * @return  boolean
     */
    public function isDiscounted()
    {
        return $this->current_price->price < $this->base_price;
    }

    /**
     * Synchronizes a Product with it's base Price model
     */
    protected function syncBasePrice()
    {
        $base = Price::firstOrNew(['product_id' => $this->id, 'discount_id' => null]);
        $base->price = $this->base_price;
        $base->save();
    }

    /**
     * Synchronizes a Product with it's discounted Price models
     */
    protected function syncDiscountedPrices()
    {
        // First, figure out which discounts this product is in the scope of
        $discounts = Discount::isActiveOrUpcoming()
            ->where(function($query) {
                $query
                    ->whereHas('products', function($product) {
                        $product->where('id', $this->id);
                    })
                    ->orWhereHas('categories', function($categories) {
                        $ids = $this->categories->lists('id');
                        $categories->whereIn('id', $ids)
                            ->orWhereHas('inherited', function($inherited) use ($ids) {
                                $inherited->whereIn('inherited_id', $ids);
                            });
                    });
            })
            ->get();

        // Next, reset all discounted price models
        $this->discounted_prices()->delete();
        foreach ($discounts as $discount) {
            Price::create([
                'product_id'    => $this->id,
                'discount_id'   => $discount->id,
                'price'         => $discount->calculate($this->base_price),
                'start_at'      => $discount->start_at,
                'end_at'        => $discount->end_at,
            ]);
        }
    }
}
