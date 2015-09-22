<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Discount;
use Bedard\Shop\Models\Product;
use Carbon\Carbon;
use DB;
use Flash;
use Lang;
use Model;
use October\Rain\Exception\ValidationException;

/**
 * Category Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\NestedTree,
        \October\Rain\Database\Traits\Validation,
        \October\Rain\Database\Traits\Sluggable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_categories';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array List of attributes to automatically generate unique URL names (slugs) for.
     */
    protected $slugs = ['slug' => 'name'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'slug',
        'filter',
        'is_hidden',
        'sort_key',
        'sort_order',
        'rows',
        'columns',
    ];

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'products' => [
            'Bedard\Shop\Models\Product',
            'table'     => 'bedard_shop_category_product',
        ],
        'discounts' => [
            'Bedard\Shop\Models\Discount',
            'table'         => 'bedard_shop_discount_category',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'name'              => 'required',
        'slug'              => 'between:3,64|unique:bedard_shop_categories',
        'rows'              => 'integer|min:0',
        'columns'           => 'integer|min:1',
        'hide_out_of_stock' => 'boolean',
        'is_hidden'         => 'boolean',
        'filter_value'      => 'numeric|min:0'
    ];

    /**
     * Model Events
     */
    public function afterUpdate()
    {
        self::syncAllCategories();
    }

    public function afterDelete()
    {
        self::syncAllCategories();
    }

    /**
     * Query Scopes
     */
    public function scopeIsFiltered($query)
    {
        // Returns filtered categories
        return $query->whereNotNull('filter');
    }

    public function scopeIsNotFiltered($query)
    {
        // Returns non-filtered categories
        return $query->whereNull('filter');
    }

    /**
     * Accessors and Mutators
     */
    public function getFilterValueAttribute()
    {
        // Floor the value if it's not a price filter
        $value = isset($this->attributes['filter_value'])
            ? $this->attributes['filter_value']
            : 0;

        return $this->filter != 'price_less' && $this->filter != 'price_greater'
            ? floor($value)
            : $value;
    }

    public function getSortAttribute()
    {
        return $this->sort_order != 'random'
            ? $this->sort_key . '-' . $this->sort_order
            : 'random';
    }

    public function setFilterAttribute($value)
    {
        $this->attributes['filter'] = $value ?: null;
    }

    public function setFilterValueAttribute($value)
    {
        $this->attributes['filter_value'] = $value ?: 0;
    }

    public function setSortAttribute($value)
    {
        if ($value == 'random') {
            $this->attributes['sort_order'] = 'random';
            $this->attributes['sort_key'] = null;
        } else {
            $parts = explode('-', $value);
            $this->attributes['sort_key'] = $parts[0];
            $this->attributes['sort_order'] = $parts[1];
        }
    }

    /**
     * Synchronizes all categories with the parent categories inheriting them
     */
    public static function syncAllCategories()
    {
        Discount::syncAllProducts();
    }
}
