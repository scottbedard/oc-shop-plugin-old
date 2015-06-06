<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

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
     * Extend the list query
     */
    public function listExtendQuery($query)
    {
        return $query->selectExtras();
    }
}
