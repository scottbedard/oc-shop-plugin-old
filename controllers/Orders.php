<?php namespace Bedard\Shop\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use Bedard\Shop\Models\Order;
use Bedard\Shop\Models\Status;
use Flash;
use Lang;

/**
 * Orders Back-end Controller
 */
class Orders extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['bedard.shop.access_orders'];

    public $bodyClass = 'compact-container';

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
        $this->vars['status_controller'] = Backend::url('bedard/shop/statuses');
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
        $query->with('events', 'status');
    }

    /**
     * Extend the form query
     *
     * @param   OctoberRainDatabaseBuilder   $query
     * @return  OctoberRainDatabaseBuilder
     */
    public function formExtendQuery($query)
    {
        $query->with('customer', 'events', 'status', 'shipping_address', 'billing_address');
    }

    /**
     * Update order statuses
     */
    public function index_onUpdateStatus()
    {
        $status = Status::find(input('status_id'));
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            $orders = Order::whereIn('id', $checkedIds)->with('events')->get();
            foreach ($orders as $order) {
                $order->changeStatus($status, null, $this->user);
            }
        }

        return $this->listRefresh();
    }

    /**
     * Overwrite the default save behavior
     */
    public function update_onSave($recordId = null, $context = null)
    {
        $status = Status::find(input('Order')['status']);

        $model = $this->formFindModelObject($recordId);
        $model->changeStatus($status, null, $this->user);
        $model->load('status', 'events');

        Flash::success(Lang::get('bedard.shop::lang.orders.update_singular', ['status' => $model->status->name]));

        return [
            '#Form-field-Order-events-group' => $this->makePartial('$/bedard/shop/models/order/_form_events.htm', [
                'formModel' => $model
            ])
        ];
    }
}
