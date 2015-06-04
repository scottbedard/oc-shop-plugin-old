<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Price;
use DB;
use Lang;
use Markdown;
use Model;
use Queue;

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
    ];

    public $hasOne = [
        'price' => [
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
     * Model Events
     */
    public function beforeSave()
    {
        // This exists to help sqlite handle a null base_price
        $this->attributes['base_price'] = $this->base_price ?: 0;
    }

    public function afterCreate()
    {
        // Create the default price model
        $price = new Price;
        $price->product_id = $this->id;
        $price->price = $this->base_price;
        $price->save();
    }

    public function afterSave()
    {
        // If the base_price has changed, we need to refresh the price models
        if ($this->getOriginal('base_price') != $this->base_price) {
            $prices = Price::with('product', 'discount')->where('product_id', $this->id)->get();
            foreach ($prices as $price) {
                $price->refresh();
            }
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
    public function scopeIsActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIsNotActive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeJoinPrice($query, $operator = false, $value = false)
    {
        $now = date('Y-m-d H:i:s');
        $subquery = "(
            SELECT `bedard_shop_prices`.`product_id`, MIN(`bedard_shop_prices`.`price`) AS `price`
            FROM `bedard_shop_prices`
            WHERE (`bedard_shop_prices`.`start_at` IS NULL OR `bedard_shop_prices`.`start_at` <= '$now')
            AND (`bedard_shop_prices`.`end_at` IS NULL OR `bedard_shop_prices`.`end_at` > '$now')
            GROUP BY `bedard_shop_prices`.`product_id`
        ) AS `price`";

        return $query->select('*')->join(DB::raw($subquery), function($join) {
            $join->on('product_id', '=', 'bedard_shop_products.id');
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

    public function setDescriptionAttribute($value)
    {
        // Cache the compiled markdown description
        $this->attributes['description'] = $value;
        $this->attributes['description_html'] = Markdown::parse(trim($value));
    }
}
