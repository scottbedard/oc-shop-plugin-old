<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Settings;
use DB;

/**
 * Products Back-end Controller
 */
class Products extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.ImportExportController',
        'Owl.Behaviors.ListDelete.Behavior',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $importExportConfig = 'config_import_export.yaml';

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
     * Override the default list refresh action to update the scoreboard as well
     *
     * @return  array
     */
    public function overrideListRefresh()
    {
        $this->prepareVars();
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
            ->addSelect('id', 'name', 'price', 'stock', 'created_at', 'updated_at')
            ->selectStatus()
            ->joinPrices()
            ->joinStock()
            ->with('current_price.discount');
    }

    /**
     * Remove smart categories from the available filters
     */
    public function listFilterExtendQuery($query, $scope)
    {
        $query->isNotFiltered();
    }

}
