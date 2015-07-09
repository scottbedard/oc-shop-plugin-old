<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Discount;
use DB;
use Flash;
use Lang;
use Owl\Widgets\TreeSort\Widget as TreeSort;

/**
 * Categories Back-end Controller
 */
class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Owl.Behaviors.ListDelete.Behavior',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bedard.shop.access_categories'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bedard.Shop', 'shop', 'categories');

        $this->addCss('/plugins/bedard/shop/assets/css/form.css');
        $this->addCss('/plugins/bedard/shop/assets/css/list.css');

        // Bind the treesort widget
        $treesort = new TreeSort($this);
        $treesort->header = Lang::get('bedard.shop::lang.categories.reorder');
        $treesort->empty = Lang::get('bedard.shop::lang.categories.reorder_empty');
        $treesort->bindToController();
    }

    /**
     * Determine if the category nesting has changed, and if so
     * re-sync the nesting and active/upcoming discounts.
     *
     * @param   Category    $category
     */
    public function formBeforeSave(Category $category)
    {
        // New categories cannot have any nesting implications
        if (!$category->id) return;

        // If nesting has changed, let the model
        if (intval(post('Category[parent_id]')) != $category->parent_id ||
            (bool) post('Category[is_inheriting]') != $category->is_inheriting) {
            $category->changedNesting = true;
        }
    }

    /**
     * Controller index
     *
     * @param   integer     $userId
     */
    public function index($userId = null)
    {
        $this->asExtension('ListController')->index();
    }

    /**
     * Extend the list query
     *
     * @param   Illuminate\Database\Query\Builder   $query
     * @return  Illuminate\Database\Query\Builder
     */
    public function listExtendQuery($query)
    {
        $query->with('products', 'inherited.products');
    }

    /**
     * Update the category tree
     */
    public function index_onUpdateTree()
    {
        foreach (input('treeData') as $i => $node) {
            DB::table(Category::make()->table)
                ->where('id', $node['id'])
                ->update([
                    'position'  => $i,
                    'parent_id' => $node['parent_id'] ?: null
                ]);
        }

        Category::syncAllCategories();
        Discount::syncAllProducts();

        return $this->listRefresh();
    }

    /**
     * Override the list delete behavior to prevent category syncing
     *
     * @param   Category    $category       The category being deleted
     */
    public function overrideListDelete(Category $category)
    {
        $category->syncAfterDelete = false;
        $category->delete();
    }

    /**
     * After the categories are deleted, syncronize category inheritance
     */
    public function afterListDelete()
    {
        Category::syncAllCategories();
        Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
    }
}
