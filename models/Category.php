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
    use \October\Rain\Database\Traits\SimpleTree,
        \October\Rain\Database\Traits\Validation;

    // public function __construct()
    // {
    //     parent::__construct();
    //     $this->setTreeOrderBy('position', 'asc');
    // }

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
        'sort_key',
        'sort_order',
        'rows',
        'columns',
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

    public function setParentIdAttribute($value)
    {
        $this->attributes['parent_id'] = $value ?: null;
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
     * Return the category's children in the correct order
     *
     * @return  Collection
     */
    public function children()
    {
        return $this
            ->getChildren()
            ->where('is_hidden', 0);
    }

    /**
     * Determines if the category has children or not
     *
     * @return  boolean
     */
    public function hasChildren()
    {
        return $this->children()->count() > 0;
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
     * Counts the number of products in a category
     *
     * @return  integer
     */
    public function countProducts()
    {
        // Count the non-paginated category
        $joinPrices = $this->filter == 'discounted' || $this->filter == 'price_less' || $this->filter == 'price_greater';

        return $this->queryProducts(false, $joinPrices)->count();
    }

    /**
     * Loads a category's products with their thumbnail images
     *
     * @param   integer     $page
     * @param   array       $relationships
     * @return  Collection
     */
    public function getProducts($page = 1, $select = [], $relationships = [])
    {
        $select = array_merge($select, ['id', 'name', 'slug', 'base_price', 'price']);

        return $this->queryProducts($page)
            ->select($select)
            ->with($relationships)
            ->get();
    }

    /**
     * Builds a query to select a category's products
     *
     * @param   integer     $page
     * @param   boolean     $joinPrices
     * @return  \October\Rain\Database\Builder
     */
    public function queryProducts($page = 1, $joinPrices = true)
    {
        // Start the product query by excluding disabled products
        $query = Product::isEnabled();

        // Join the price table if needed
        if ($joinPrices) {
            $query->joinPrices();
        }

        // Hide out of stock products if needed
        if ($this->hide_out_of_stock) {
            $query->inStock();
        }

        // If this is not a filtered category, load the products that are
        // directly related, or are inherited from a child category.
        if (!$this->filter) {
            $query->whereHas('categories', function($category) {
                $category->where(function($condition) {
                    $condition
                        ->where('id', $this->id)
                        ->orWhereHas('inherited_by', function($inherited_by) {
                            $inherited_by->where('parent_id', $this->id);
                        });
                });
            });
        }

        // Otherwise, add the filter logic to the query
        else {
            // For the "all" filter, do nothing
            if ($this->filter == 'discounted') {
                $query->wherePrice('<', 'base_price');
            } elseif ($this->filter == 'created_less') {
                $query->where('created_at', '>', Carbon::now()->subDay($this->filter_value));
            } elseif ($this->filter == 'created_greater') {
                $query->where('created_at', '<', Carbon::now()->subDay($this->filter_value));
            } elseif ($this->filter == 'price_less') {
                $query->wherePrice('<', $this->filter_value);
            } elseif ($this->filter == 'price_greater') {
                $query->wherePrice('>', $this->filter_value);
            }
        }

        // Sort and paginate the results if needed
        if ($page !== false) {
            if ($this->sort_order == 'random') {
                $query->orderBy(DB::raw('RAND()')); // todo: use a better random sort query
                $page = 1;
            } elseif ($this->sort_key && $this->sort_order) {
                $query->orderBy($this->sort_key, $this->sort_order);
            }
            if ($this->rows > 0) {
                $perpage = $this->rows * $this->columns;
                $query
                    ->skip(($perpage * $page) - $perpage)
                    ->take($perpage);
            }
        }

        // Return the query
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
