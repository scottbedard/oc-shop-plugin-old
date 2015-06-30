<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Settings;
use DB;
use Flash;
use Lang;

/**
 * Products Back-end Controller
 */
class Products extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bedard.shop.access_products'];

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bedard.Shop', 'shop', 'products');

        $this->addCss('/plugins/bedard/shop/assets/css/form.css');
        $this->addCss('/plugins/bedard/shop/assets/css/list.css');
    }

    /**
     * Load scoreboard data
     */
    public function prepareVars()
    {
        $this->vars['total']        = Product::count();
        $this->vars['normal']       = Product::isEnabled()->isNotDiscounted()->count();
        $this->vars['discounted']   = Product::isEnabled()->isDiscounted()->count();
        $this->vars['disabled']     = Product::isDisabled()->count();
        $this->vars['instock']      = Product::inStock()->count();
    }

    /**
     * Refreshes the list and scoreboard
     *
     * @return  array
     */
    private function refreshListAndScoreboard()
    {
        $this->prepareVars();
        $this->asExtension('ListController')->index();

        $array = $this->listRefresh();
        $array['#products-scoreboard'] = $this->makePartial('list_scoreboard');

        return $array;
    }

    /**
     * List index
     */
    public function index($userId = null)
    {
        $this->prepareVars();
        $this->asExtension('ListController')->index();
    }

    /**
     * Extend the form fields
     */
    public function formExtendFields($form)
    {
        // Add the inventories widget if the user has access
        if ($this->user->hasAccess('bedard.shop.access_inventories')) {
            $form->addSecondaryTabFields([
                'optionsinventories' => [
                    'tab'   => 'bedard.shop::lang.products.options_inventories',
                    'type'  => 'optionsinventories',
                ],
            ]);
        }

        // Only show one type of editor
        if (Settings::getEditor() == 'code') {
            $form->removeField('description_html');
            $form->removeField('snippet_html');
        } else {
            $form->removeField('description');
            $form->removeField('snippet');
        }
    }

    /**
     * If the categories or base price has changed, we need to let
     * the model know so prices can be re-calculated after saving.
     *
     * @param   Product $product
     */
    public function formBeforeSave(Product $product)
    {
        $newCategories = post('Product[categories]') ?: [];
        $oldCategories = $product->categories->lists('id') ?: [];
        if (!$product->id ||
            array_diff($newCategories, $oldCategories) ||
            array_diff($oldCategories, $newCategories)) {
            $product->changedCategories = true;
        }
    }

    /**
     * Extend the list query
     *
     * @param   October\Rain\Database\Builder   $query
     * @return  October\Rain\Database\Builder
     */
    public function listExtendQuery($query)
    {
        $query
            ->joinPrices()
            ->with('current_price.discount');
    }

    /**
     * Remove smart categories from the available filters
     */
    public function listFilterExtendQuery($query, $scope)
    {
        $query->isNotFiltered();
    }

    /**
     * Delete selected rows
     *
     * @return  array
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $postId) {
                if ($model = Product::find($postId)) {
                    $model->delete();
                }
            }
            Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
        }

        return $this->refreshListAndScoreboard();
    }
}
