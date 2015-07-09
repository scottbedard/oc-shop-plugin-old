<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Promotion;
use DB;

/**
 * Promotions Back-end Controller
 */
class Promotions extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Owl.Behaviors.ListDelete.Behavior',
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
     * Extend the list query
     *
     * @param   October\Rain\Database\Builder   $query
     * @return  October\Rain\Database\Builder
     */
    public function listExtendQuery($query)
    {
        return $query
            ->select('*')
            ->selectStatus()
            ->selectCartAmount()
            ->selectShippingAmount();
    }
}
