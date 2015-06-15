<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use DB;

/**
 * Discounts Back-end Controller
 */
class Discounts extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bedard.shop.access_discounts'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bedard.Shop', 'shop', 'discounts');

        $this->addCss('/plugins/bedard/shop/assets/css/form.css');
        $this->addCss('/plugins/bedard/shop/assets/css/list.css');
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
                    `bedard_shop_discounts`.`end_at` IS NOT NULL AND
                    `bedard_shop_discounts`.`end_at` < '$now'
                ) THEN 2
                WHEN (
                    `bedard_shop_discounts`.`start_at` IS NOT NULL AND
                    `bedard_shop_discounts`.`start_at` > '$now'
                ) THEN 1
                ELSE 0
            END
        ) as `status`";

        $amount = "(
            CASE
                WHEN `bedard_shop_discounts`.`is_percentage` = 1 THEN `bedard_shop_discounts`.`amount_percentage`
                ELSE `bedard_shop_discounts`.`amount_exact`
            END
        ) as `amount`";

        return $query->select(DB::raw("*, $status, $amount"));
    }
}
