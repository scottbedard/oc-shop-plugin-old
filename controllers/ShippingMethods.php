<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Shipping Methods Back-end Controller
 */
class ShippingMethods extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
        'Bedard.Shop.Behaviors.ListActions',
    ];

    public $formConfig      = 'config_form.yaml';
    public $listConfig      = 'config_list.yaml';
    public $relationConfig  = 'config_relation.yaml';

    public $requiredPermissions = ['bedard.shop.access_shipping_table'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bedard.Shop', 'shop', 'shippingmethods');

        $this->addCss('/plugins/bedard/shop/assets/css/form.css');
        $this->addCss('/plugins/bedard/shop/assets/css/list.css');
    }
}
