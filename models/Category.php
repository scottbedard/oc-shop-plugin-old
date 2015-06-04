<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Classes\CategoryFilters;
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
        'name'  => 'required',
        'slug'  => 'required|between:3,64|unique:bedard_shop_categories',
    ];

    /**
     * @var boolean     Determines if categories should be synchronized after delete
     */
    public $syncAfterDelete = true;

    /**
     * Model Events
     */
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

    public function afterCreate()
    {
        // Add the new category to it's parent's inherited categories
        self::clearTreeCache();
        $this->syncParents();
    }

    public function afterUpdate()
    {
        // If the nesting or inheritance has changed, synchronize all categories
        $original = $this->getOriginal();
        $parent_id = isset($original['parent_id']) ? $original['parent_id'] : null;
        $is_inheriting = isset($original['is_inheriting']) ? $original['is_inheriting'] : true;
        if ($parent_id != $this->parent_id || $is_inheriting != $this->is_inheriting) {
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

        if ($this->syncAfterDelete) self::syncAllCategories();
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
     * Return a list of eligible parent categories
     *
     * @return  array
     */
    public function getParentOptions()
    {
        $options = [Lang::get('bedard.shop::lang.categories.parent_empty')];

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
    }
}
