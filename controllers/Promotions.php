<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Promotion;
use DB;
use Flash;
use Lang;

/**
 * Promotions Back-end Controller
 */
class Promotions extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bedard.shop.access_promotions'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bedard.Shop', 'shop', 'promotions');

        $this->addCss('/plugins/bedard/shop/assets/css/form.css');
        $this->addCss('/plugins/bedard/shop/assets/css/list.css');
        $this->addJs('/plugins/bedard/shop/assets/js/relation-selector-form.js');
    }

    /**
     * This selects the discount's amount and status. It is done here
     * rather than from the YAML files to avoid using "now()", which
     * is unsupported by SQLite.
     */
    public function listExtendQuery($query)
    {
        $now = date('Y-m-d H:i:s');

        $status = "(
            CASE
                WHEN (
                    `bedard_shop_promotions`.`end_at` IS NOT NULL AND
                    `bedard_shop_promotions`.`end_at` < '$now'
                ) THEN 2
                WHEN (
                    `bedard_shop_promotions`.`start_at` IS NOT NULL AND
                    `bedard_shop_promotions`.`start_at` >= '$now'
                ) THEN 1
                ELSE 0
            END
        ) as `status`";

        $cart = "(
            CASE
                WHEN `bedard_shop_promotions`.`is_cart_percentage` = 1 THEN `bedard_shop_promotions`.`cart_percentage`
                ELSE `bedard_shop_promotions`.`cart_exact`
            END
        ) as `cart_amount`";

        $shipping = "(
            CASE
                WHEN `bedard_shop_promotions`.`is_shipping_percentage` = 1 THEN `bedard_shop_promotions`.`shipping_percentage`
                ELSE `bedard_shop_promotions`.`shipping_exact`
            END
        ) as `shipping_amount`";

        return $query->select(DB::raw("*, $status, $cart, $shipping"));
    }

    /**
     * Delete selected rows
     */
    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            foreach ($checkedIds as $postId) {
                if ($model = Promotion::find($postId)) {
                    $model->delete();
                }
            }
            Flash::success(Lang::get('backend::lang.list.delete_selected_success'));
        }
        return $this->listRefresh();
    }
}
