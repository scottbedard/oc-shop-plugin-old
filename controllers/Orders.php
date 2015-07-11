<?php namespace Bedard\Shop\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\Status;

/**
 * Orders Back-end Controller
 */
class Orders extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bedard.shop.access_orders'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Bedard.Shop', 'shop', 'orders');

        $this->addCss('/plugins/bedard/shop/assets/css/form.css');
        $this->addCss('/plugins/bedard/shop/assets/css/list.css');
    }

    public function prepareVars()
    {
        $this->vars['statuses'] = Status::all();
    }

    public function index($userId = null)
    {
        $this->prepareVars();
        $this->asExtension('ListController')->index();
    }

    /**
     * Extend the list query
     *
     * @param   OctoberRainDatabaseBuilder   $query
     * @return  OctoberRainDatabaseBuilder
     */
    public function listExtendQuery($query)
    {
        $query->with('events.status');
    }

    /**
     * Update order statuses
     */
    public function index_onUpdateStatus()
    {
        $status_id = input('status_id');
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            $orders = Order::whereIn('id', $checkedIds)->with('events')->get();
            foreach ($orders as $order) {
                $order->changeStatus($status_id, $this->user->id);
            }
        }

        return $this->listRefresh();
    }
}
