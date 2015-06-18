<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Product;
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
        $this->vars['normal']       = Product::isActive()->isNotDiscounted()->count();
        $this->vars['discounted']   = Product::isActive()->isDiscounted()->count();
        $this->vars['disabled']     = Product::isNotActive()->count();
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
        if ($this->user->hasAccess('bedard.shop.access_inventories')) {
            $form->addSecondaryTabFields([
                'optionsinventories' => [
                    'tab'   => 'bedard.shop::lang.products.options_inventories',
                    'type'  => 'optionsinventories',
                ],
            ]);
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
        $now = date('Y-m-d H:i:s');

        $price_table = "(
            SELECT
                `bedard_shop_prices`.`product_id` as `price_product_id`,
                MIN(`bedard_shop_prices`.`price`) AS `price`
            FROM `bedard_shop_prices`
            WHERE (`bedard_shop_prices`.`start_at` IS NULL OR `bedard_shop_prices`.`start_at` <= '$now')
            AND (`bedard_shop_prices`.`end_at` IS NULL OR `bedard_shop_prices`.`end_at` > '$now')
            GROUP BY `bedard_shop_prices`.`product_id`
        ) AS `price`";

        $inventory = "(
            SELECT SUM(`bedard_shop_inventories`.`quantity`)
            FROM `bedard_shop_inventories`
            WHERE `bedard_shop_inventories`.`product_id` = `bedard_shop_products`.`id`
        ) as `inventory`";

        $status = "(
            CASE
                WHEN (`bedard_shop_products`.`is_active` = 0) THEN 0
                WHEN (`price` < `bedard_shop_products`.`base_price`) THEN 2
                ELSE 1
            END
        ) as `status`";

        $query
            ->select(DB::raw("*, $inventory, $status"))
            ->join(DB::raw($price_table), function($join) {
                $join->on('price_product_id', '=', 'bedard_shop_products.id');
            });

        // Also eager load the normal relationships
        return $query->with('current_price.discount');
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
