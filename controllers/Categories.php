<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Category;
use Bedard\Shop\Widgets\ReorderCategories;
use Flash;
use Lang;

/**
 * Categories Back-end Controller
 */
class Categories extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
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

        // Bind the reorder widget
        $reorderCategories = new ReorderCategories($this);
        $reorderCategories->bindToController();
    }

    /**
     * Controller index
     *
     * @param   integer     $userId
     */
    public function index($userId = null)
    {
        $this->widget->reorderCategories->injectAssets();
        $this->asExtension('ListController')->index();
    }

    /**
     * Delete selected rows
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $postId) {
                if ($model = Category::find($postId)) {
                    $model->delete();
                }
            }
            Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
        }
        return $this->listRefresh();
    }
}
