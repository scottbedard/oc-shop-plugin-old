<?php namespace Bedard\Shop\Models;

use Carbon\Carbon;
use Db;
use Lang;
use Markdown;
use Model;
use Bedard\Shop\Classes\WeightHelper;

/**
 * Product Model
 */
class Product extends Model
{
    use \Bedard\Shop\Traits\CartCacheTrait,
        \October\Rain\Database\Traits\Purgeable,
        \October\Rain\Database\Traits\Validation,
        \October\Rain\Database\Traits\Sluggable;

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
        'is_enabled',
    ];

    /**
     * @var array   Cacheable fields
     */
    public $cacheable = [
        'id',
        'name',
        'base_price',
        'price',
    ];

    /**
     * @var array   Attribute casting
     */
    public $casts = [
        'base_price'    => 'float',
        'price'         => 'float',
    ];

    /**
     * @var array Purgeable fields
     */
    protected $purgeable = ['optionsinventories'];

    /**
     * @var array List of attributes to automatically generate unique URL names (slugs) for.
     */
    protected $slugs = ['slug' => 'name'];

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
        'promotions' => [
            'Bedard\Shop\Models\Promotion',
            'table' => 'bedard_shop_product_promotion',
        ],
        'discounts' => [
            'Bedard\Shop\Models\Discount',
            'table' => 'bedard_shop_discount_product',
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
            'scope' => 'isRunning',
            'order' => 'price asc',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'name'          => 'required',
        'slug'          => 'between:3,64|unique:bedard_shop_products',
        'base_price'    => 'numeric|min:0',
    ];

    public $customMessages = [
        'base_price.min'        => 'bedard.shop::lang.products.base_price_min',
        'base_price.numeric'    => 'bedard.shop::lang.products.base_price_numeric',
    ];

    /**
     * The attributes on which the product list can be ordered
     *
     * @var array
     */
    public static $allowedSortingOptions = [
        'name asc'        => 'Name (A-Z)',
        'name desc'       => 'Name (Z-A)',
        'created_at asc'  => 'Date created (Oldest first)',
        'created_at desc' => 'Date created (Newest first)',
        'price asc'       => 'Price (Lowest to highest)',
        'price desc'      => 'Price (Highest to lowest)'
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

    public function afterSave()
    {
        // Sync the base price if it has changed
        if ($this->changedPrice) {
            $this->syncBasePrice();
        }

        // Sync the discount prices if the base_price has changed
        if ($this->changedPrice || $this->changedCategories) {
            $this->syncDiscountedPrices();
        }
    }

    public function afterDelete()
    {
        // Keep pivot tables clean
        Db::table($this->belongsToMany['categories']['table'])
            ->where('product_id', $this->attributes['id'])
            ->delete();

        // Clean up prices
        Price::where('product_id', $this->id)->delete();
    }

    /**
     * Query Scopes
     *
     * @param   October\Rain\Database\Builder   $query
     * @return  October\Rain\Database\Builder
     */
    public function scopeFilterByCategory($query, $filter)
    {
        return $query->whereHas('categories', function($category) use ($filter) {
            $category->whereIn('id', $filter);
        });
    }

    public function scopeInStock($query)
    {
        return $query->whereHas('inventories', function($inventory) {
            $inventory->where('quantity', '>', 0);
        });
    }

    public function scopeOutOfStock($query)
    {
        $query->whereDoesntHave('inventories', function($inventory) {
            $inventory->where('quantity', '>', 0);
        });
    }

    public function scopeIsEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeIsDisabled($query)
    {
        return $query->where('is_enabled', false);
    }

    public function scopeIsDiscounted($query)
    {
        return $query->whereHas('current_price', function($price) {
            $price
                ->isRunning()
                ->whereRaw('`bedard_shop_prices`.`price` <> `bedard_shop_products`.`base_price`');
        });
    }

    public function scopeIsNotDiscounted($query)
    {
        return $query->whereDoesntHave('current_price', function($price) {
            $price
                ->isRunning()
                ->whereRaw('`bedard_shop_prices`.`price` < `bedard_shop_products`.`base_price`');
        });
    }

    public function scopeJoinPrices($query)
    {
        // Joins a price table
        $now = date('Y-m-d H:i:s');
        $prices = "(".
            "SELECT `bedard_shop_prices`.`product_id`, `bedard_shop_prices`.`discount_id`, MIN(`bedard_shop_prices`.`price`) AS `price` ".
            "FROM `bedard_shop_prices` ".
            "WHERE (`bedard_shop_prices`.`start_at` IS NULL OR `bedard_shop_prices`.`start_at` <= '$now') ".
            "AND (`bedard_shop_prices`.`end_at` IS NULL OR `bedard_shop_prices`.`end_at` > '$now') ".
            "GROUP BY `bedard_shop_prices`.`product_id`".
        ") AS `prices`";

        return $query
            ->join(Db::raw($prices), 'prices.product_id', '=', 'bedard_shop_products.id');
    }

    public function scopeJoinStock($query)
    {
        // Joins a stock table
        $stock = '('.
            'SELECT `bedard_shop_inventories`.`product_id`, SUM(`bedard_shop_inventories`.`quantity`) AS `stock` '.
            'FROM `bedard_shop_inventories` '.
            'GROUP BY `bedard_shop_inventories`.`product_id`'.
        ') AS `stocks`';

        return $query
            ->leftJoin(Db::raw($stock), 'stocks.product_id', '=', 'bedard_shop_products.id');
    }

    public function scopeSelectStatus($query)
    {
        // Select the product's status
        return $query
            ->addSelect(Db::raw(
                '('.
                    'CASE '.
                        'WHEN (`bedard_shop_products`.`is_enabled` = 0) THEN 0 '.
                        'WHEN (`price` < `bedard_shop_products`.`base_price`) THEN 2 '.
                        'ELSE 1 '.
                    'END'.
                ') as `status`'
            ));
    }

    public function scopeWherePrice($query, $operator, $amount)
    {
        $amount = $amount == 'base_price'
            ? '`bedard_shop_products`.`base_price`'
            : floatval($amount);

        return $query->where('price', $operator, Db::raw($amount));
    }

    /**
     * Lists products for the front end
     *
     * @param  array $options Display options
     * @return self
     */
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page'      => 1,
            'perPage'   => 30,
            'sort'      => 'created_at',
            'category'  => null,
            'search'    => ''
        ], $options));

        $query->joinPrices();

        /*
         * Sorting
         */
        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {
            if (in_array($_sort, array_keys(self::$allowedSortingOptions))) {
                $parts = explode(' ', $_sort);
                if (count($parts) < 2) {
                    array_push($parts, 'desc');
                }

                list($sortField, $sortDirection) = $parts;
                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, ['name', 'description']);
        }

        /*
         * Category
         */
        if ($category !== null) {
            $category = Category::find($category);

            if ($filter = $category->filter) {
                if ($filter == 'discounted') {
                    $query->wherePrice('<', 'base_price');
                }
                elseif ($filter == 'created_less') {
                    $query->where('created_at', '>', Carbon::now()->subDay($category->filter_value));
                }
                elseif ($filter == 'created_greater') {
                    $query->where('created_at', '<', Carbon::now()->subDay($category->filter_value));
                }
                elseif ($filter == 'price_less') {
                    $query->wherePrice('<', $category->filter_value);
                }
                elseif ($filter == 'price_greater') {
                    $query->wherePrice('>', $category->filter_value);
                }
            }
            else {
                $categories = $category->getAllChildrenAndSelf()->lists('id');
                $query->whereHas('categories', function($q) use ($categories) {
                    $q->whereIn('shop_categories.id', $categories);
                });
            }

            if ($category->hide_out_of_stock) {
                $query->inStock();
            }
        }

        return $query->paginate($perPage, $page);
    }

    /**
     * Accessors & Mutators
     */
    public function getBasePriceAttribute()
    {
        // Helper to allow basePrice and base_price to work
        return isset($this->attributes['base_price'])
            ? $this->attributes['base_price']
            : 0;
    }

    public function getIsDiscountedAttribute()
    {
        return $this->price < $this->base_price;
    }

    public function getIsInStockAttribute()
    {
        return $this->inventories->sum('quantity') > 0;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->inventories->sum('quantity') <= 0;
    }

    public function getPriceAttribute()
    {
        // If the price has been joined to the query, return it. Otherwise grab
        // the price from the current_price relationship.
        return is_array($this->attributes) && array_key_exists('price', $this->attributes)
            ? $this->attributes['price']
            : $this->current_price->price;
    }

    public function getStockAttribute($value)
    {
        return is_array($this->attributes) && array_key_exists('stock', $this->attributes)
            ? intval($this->attributes['stock'])
            : null;
    }

    public function setBasePriceAttribute($value)
    {
        // Keep track of when the price changes
        if (!isset($this->attributes['base_price']) || $value != $this->attributes['base_price']) {
            $this->changedPrice = true;
        }

        $this->attributes['base_price'] = $value ?: 0;
    }

    public function setDescriptionAttribute($value)
    {
        // Parse code description
        $this->attributes['description'] = $value;
        $this->attributes['description_html'] = Markdown::parse(trim($value));
    }

    public function setSnippetAttribute($value)
    {
        // Parse the code snippet
        $this->attributes['snippet'] = $value;
        $this->attributes['snippet_html'] = Markdown::parse(trim($value));
    }

    public function setWeightAttribute($value)
    {
        $this->attributes['weight'] = $value ?: 0;
    }

    /**
     * Filter the form fields
     */
    public function filterFields($fields, $context = null)
    {
        $fields->weight->comment = Lang::get('bedard.shop::lang.products.weight_comment', [
            'units' => WeightHelper::getPlural()
        ]);
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
        $discounts = Discount::isRunningOrUpcoming()
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

    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param string $pageName
     * @param \Cms\Classes\Controller $controller
     * @return string
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }
}
