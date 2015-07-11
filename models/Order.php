<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\OrderEvent;
use Bedard\Shop\Models\Status;
use Carbon\Carbon;
use DB;
use Lang;
use Model;

/**
 * Payment Model
 */
class Order extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_orders';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'cart_id',
        'customer_id',
        'shipping_address_id',
        'billing_address_id',
        'shipping_driver_id',
        'payment_driver_id',
        'cart_cache',
        'cart_subtotal',
        'shipping_total',
        'promotion_total',
        'payment_total',
        'status_id',
        'status_at',
    ];

    /**
     * Jsonable fields
     */
    protected $jsonable = ['cart_cache'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'customer' => [
            'Bedard\Shop\Models\Customer',
        ],
        'status' => [
            'Bedard\Shop\Models\Status',
        ],
    ];

    public $hasMany = [
        'events' => [
            'Bedard\Shop\Models\OrderEvent',
            'order' => 'created_at',
        ],
    ];

    /**
     * Accessors and Mutators
     */
    public function setCartSubtotalAttribute($value)
    {
        $this->attributes['cart_subtotal'] = $value ?: 0;
    }

    public function setPaymentTotalAttribute($value)
    {
        $this->attributes['payment_total'] = $value ?: 0;
    }

    public function setPromotionTotalAttribute($value)
    {
        $this->attributes['promotion_total'] = $value ?: 0;
    }

    public function setShippingTotalAttribute($value)
    {
        $this->attributes['shipping_total'] = $value ?: 0;
    }

    /**
     * Change the order's status
     *
     * @param   integer     $status_id      The new status
     * @param   integer     $driver         Driver making the status change
     * @param   integer     $user           Backend user making the status change
     */
    public function changeStatus($status_id, $driver = null, $user = null)
    {
        if ($this->status_id == $status_id) {
            return;
        }

        $this->status_id = $status_id;
        $this->status_at = Carbon::now();
        $this->save();

        $author = null;
        if (!is_null($driver)) {
            $author = $driver->name;
        } elseif (!is_null($user)) {
            $author = $user->fullName;
        }

        $status = ($newStatus = Status::find($status_id))
            ? $newStatus->name
            : 'bedard.shop::lang.orders.status_unknown';

        OrderEvent::create([
            'order_id'  => $this->id,
            'user_id'   => $user ? $user->id : null,
            'driver_id' => $driver ? $driver->id : null,
            'message'   => Lang::get('bedard.shop::lang.orders.status_changed', [
                'status' => '<strong>'.Lang::get($status).'</strong>',
                'author' => Lang::get($author),
            ]),
        ]);
    }

    /**
     * Return the status options with their icons
     *
     * @return  array
     */
    public function getStatusOptions()
    {
        $options = [];
        foreach (Status::all() as $status) {
            $options[$status->id] = '<i class="status-icon '.$status->icon.' '.$status->class.'"></i> '.Lang::get($status->name);
        }

        return $options;
    }

}
