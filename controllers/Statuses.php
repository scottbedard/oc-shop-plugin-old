<?php namespace Bedard\Shop\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;

/**
 * Statuses Back-end Controller
 */
class Statuses extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Owl.Behaviors.ListDelete.Behavior',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bedard.Shop', 'shop', 'orders');

        $this->addCss('/plugins/bedard/shop/assets/css/form.css');
        $this->addCss('/plugins/bedard/shop/assets/css/list.css');
    }

    public function prepareVars()
    {
        $this->vars['orders_controller'] = Backend::url('bedard/shop/orders');
    }

    public function index()
    {
        $this->prepareVars();
        $this->asExtension('ListController')->index();
    }
}
