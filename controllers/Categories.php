<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Category;
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
        return $this->listRefresh();
    }

    /**
     * Delete selected rows
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $postId) {
                if ($model = Category::find($postId)) {
                    $model->syncAfterDelete = false;
                    $model->delete();
                }
            }
            Category::syncAllCategories();
            Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
        }
        return $this->listRefresh();
    }
}
