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
     * If the categories or base price have changed, we need to let
     * the modelknow so prices can be re-calculated after saving.
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
     * This joins a price table to the product table. It is done here instead
     * of from the YAML file to avoid using "now()", which is unsupported
     * by SQLite.
     */
    public function listExtendQuery($query)
    {
        $now = date('Y-m-d H:i:s');

        $price_table = "(
            SELECT `bedard_shop_prices`.`product_id`, MIN(`bedard_shop_prices`.`price`) AS `price`
            FROM `bedard_shop_prices`
            WHERE (`bedard_shop_prices`.`start_at` IS NULL OR `bedard_shop_prices`.`start_at` <= '$now')
            AND (`bedard_shop_prices`.`end_at` IS NULL OR `bedard_shop_prices`.`end_at` > '$now')
            GROUP BY `bedard_shop_prices`.`product_id`
        ) AS `price`";

        $query
            ->select('*')
            ->join(DB::raw($price_table), function($join) {
                $join->on('product_id', '=', 'bedard_shop_products.id');
            });

        // Also eager load the normal product relationships
        return $query->with('current_price');
    }

    /**
     * Delete selected rows
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
        return $this->listRefresh();
    }
}
