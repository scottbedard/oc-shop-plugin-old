<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Category;
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
    use \October\Rain\Database\Traits\Validation;

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
     * @var array Relations
     */
    public $belongsToMany = [
        'categories' => [
            'Bedard\Shop\Models\Category',
            'table' => 'bedard_shop_cat_prod',
        ],
        'displayCategories' => [
            'Bedard\Shop\Models\Category',
            'table' => 'bedard_shop_cat_prod_display',
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
    public function afterSave()
    {
        // Synchronize the product's display categories
        $this->syncCategories();
    }

    public function afterDelete()
    {
        // Keep pivot tables clean
        DB::table($this->belongsToMany['categories']['table'])
            ->where('product_id', $this->attributes['id'])
            ->delete();

        DB::table($this->belongsToMany['displayCategories']['table'])
            ->where('product_id', $this->attributes['id'])
            ->delete();
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
        // Some databases don't use the default value when a decimal
        // column is filled with an empty string. This will ensure
        // there is always a value entered for the base_price.
        $this->attributes['base_price'] = $value ?: 0;
    }

    public function setDescriptionAttribute($value)
    {
        // Cache the compiled markdown description
        $this->attributes['description'] = $value;
        $this->attributes['description_html'] = Markdown::parse(trim($value));
    }

    /**
     * Synchronizes a product with the categories it should be displayed in.
     * This takes into account category nesting, and product inheritance.
     */
    public function syncCategories()
    {
        $display = $this->categories->lists('id');

        if ($display) {
            Category::clearTreeCache();
            foreach ($this->categories as $category) {
                foreach (array_reverse($category->getParents()) as $parent) {
                    if (!$parent->is_inheriting || $parent->filter !== null) break;
                    $display[] = $parent->id;
                }
            }
        }

        $this->displayCategories()->sync($display);
    }

    /**
     * Queues the synchronization of multiple product's category display
     *
     * @param   Illuminate\Database\Eloquent\Collection $products
     * @return  void
     */
    public static function syncProducts($products)
    {
        foreach ($products as $product) {
            $id = $product->id;
            Queue::push(function($job) use ($id) {
                $product = Product::with('categories')->find($id);
                $product->syncCategories();
            });
        }
    }
}
