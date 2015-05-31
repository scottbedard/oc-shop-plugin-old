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
        'products' => [
            'Bedard\Shop\Models\Product',
            'table' => 'bedard_shop_category_product',
        ],
    ];
    public $hasMany = [
        'children' => [
            'Bedard\Shop\Models\Category',
            'key' => 'parent_id',
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

        foreach ($categories as $category) {
            $delimeter = '<i class="delimeter icon-angle-right"></i>';
            $branch = [];
            foreach ($category->getParents() as $parent) {
                $branch[] = '<span class="parent">' . $parent->name . '</span>';
            }
            $branch[] = $category->name;
            $options[$category->id] = implode($delimeter, $branch);
        }

        return $options;
    }
}
