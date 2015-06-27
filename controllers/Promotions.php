<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

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
    }
}
