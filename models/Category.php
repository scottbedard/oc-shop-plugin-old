<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Discount;
use Bedard\Shop\Models\Product;
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
    use \October\Rain\Database\Traits\SimpleTree,
        \October\Rain\Database\Traits\Validation;

    function __construct()
    {
        parent::__construct();
        $this->setTreeOrderBy('position', 'asc');
    }

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_categories';

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
        'parent_id',
        'filter',
        'is_inheriting',
        'is_hidden',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'parent' => [
            'Bedard\Shop\Models\Category',
        ],
    ];

    public $belongsToMany = [
        'inherited' => [
            'Bedard\Shop\Models\Category',
            'table'     => 'bedard_shop_category_inheritance',
            'key'       => 'parent_id',
            'otherKey'  => 'inherited_id',
        ],
        'inherited_by' => [
            'Bedard\Shop\Models\Category',
            'table'     => 'bedard_shop_category_inheritance',
            'key'       => 'inherited_id',
            'otherKey'  => 'parent_id',
        ],
        'products' => [
            'Bedard\Shop\Models\Product',
            'table'     => 'bedard_shop_category_product',
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
        'name'              => 'required',
        'slug'              => 'required|between:3,64|unique:bedard_shop_categories',
        'rows'              => 'integer|min:0',
        'columns'           => 'integer|min:1',
        'hide_out_of_stock' => 'boolean',
        'is_hidden'         => 'boolean',
        'filter_value'      => 'numeric|min:0'
    ];

    /**
     * @var boolean     Determines if categories should be synchronized after delete
     */
    public $syncAfterDelete = true;

    /**
     * @var boolean     Determines if the nesting of the category has changed
     */
    public $changedNesting = false;

    /**
     * Model Events
     */
    public function afterCreate()
    {
        // Add the new category to it's parent's inherited categories
        self::clearTreeCache();
        $this->syncParents();
    }

    public function beforeUpdate()
    {
        // Prevent categories from being nested in their own tree
        self::clearTreeCache();
        if ($this->parent_id == $this->id || $this->getAllChildren()->find($this->parent_id)) {
            $error = Lang::get('bedard.shop::lang.categories.invalid_parent');
            Flash::error($error);
            throw new ValidationException($error);
        }
    }

    public function afterUpdate()
    {
        // If the nesting has changed, sync everything
        if ($this->changedNesting) {
            self::syncAllCategories();
        }
    }

    public function afterDelete()
    {
        // Remove the parent_id of child categories after delete
        DB::table($this->table)
            ->where('parent_id', $this->attributes['id'])
            ->update(['parent_id' => null]);

        // Keep pivot tables clean
        DB::table($this->belongsToMany['products']['table'])
            ->where('category_id', $this->attributes['id'])
            ->delete();

        // Sync the categories
        if ($this->syncAfterDelete) {
            self::syncAllCategories();
        }
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

    public function setFilterAttribute($value)
    {
        $this->attributes['filter'] = $value ?: null;
    }

    public function setFilterValueAttribute($value)
    {
        $this->attributes['filter_value'] = $value ?: 0;
    }

    /**
     * Return a list of eligible parent categories
     *
     * @return  array
     */
    public function getParentIdOptions()
    {
        $options = [Lang::get('bedard.shop::lang.categories.none')];

        $categories = Category::whereNotIn('id', $this->getAllChildren()->lists('id'))
            ->where('id', '<>', $this->id ?: 0)
            ->orderBy('position', 'asc')
            ->get();

        $delimeter = '<i class="delimeter icon-angle-right"></i>';
        foreach ($categories as $category) {
            $branch = [];
            foreach ($category->getParents() as $parent) {
                $branch[] = '<span class="parent">' . $parent->name . '</span>';
            }
            $branch[] = $category->name;
            $options[$category->id] = implode($delimeter, $branch);
        }

        return $options;
    }

    /**
     * Builds a query to select a category's products
     *
     * @return  \October\Rain\Database\Builder
     */
    public function queryProducts()
    {
        // Start the product query by excluding disabled products
        $query = Product::isActive();

        // Hide out of stock products if needed
        if ($this->hide_out_of_stock) {
            $query->inStock();
        }

        // If this is not a filtered category, load the products that are
        // directly related, or are inherited from a child category.
        if (!$this->filter) {
            $query->whereHas('categories', function($category) {
                $category->where('id', $this->id)
                    ->orWhereHas('inherited_by', function($inherited_by) {
                        $inherited_by->where('parent_id', $this->id);
                    });
            });
        }

        // Return the results
        return $query;
    }

    /**
     * Synchronize a category with parent categories inheriting it
     */
    public function syncParents()
    {
        foreach (array_reverse($this->getParents()) as $parent) {
            if (!$parent->is_inheriting) break;
            $parent->inherited()->add($this);
        }
    }

    /**
     * Synchronizes all categories with the parent categories inheriting them
     */
    public static function syncAllCategories()
    {
        self::clearTreeCache();

        DB::table(self::make()->belongsToMany['inherited']['table'])->truncate();
        foreach (self::all() as $category) {
            $category->syncParents();
        }

        Discount::syncAllProducts();
    }
}
