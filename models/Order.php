<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Classes\CartCache;
use Bedard\Shop\Models\Driver;
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
     * @var array Attribute type casting
     */
    public $casts = [
        'is_paid'   => 'boolean',
    ];

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
        'is_paid',
    ];

    /**
     * Jsonable fields
     */
    protected $jsonable = ['cart_cache'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'billing_address' => [
            'Bedard\Shop\Models\Address',
            'key' => 'billing_address_id',
        ],
        'cart' => [
            'Bedard\Shop\Models\Cart',
        ],
        'customer' => [
            'Bedard\Shop\Models\Customer',
        ],
        'payment_driver' => [
            'Bedard\Shop\Models\Driver',
            'key' => 'payment_driver_id',
        ],
        'shipping_address' => [
            'Bedard\Shop\Models\Address',
            'key' => 'shipping_address_id',
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
     * Query Scopes
     *
     * @param   October\Rain\Database\Builder   $query
     * @return  October\Rain\Database\Builder
     */
    public function scopeFilterByStatus($query, $filter)
    {
        return $query->whereIn('status_id', $filter);
    }

    public function scopeShouldBeAbandoned($query, $minutes)
    {
        return $query->where(function($query) use ($minutes) {
            $query
                ->where('is_paid', false)
                ->where('status_at', '<', Carbon::now()->subMinutes($minutes))
                ->whereHas('status', function($status) {
                    $status->where('core_status', 'started');
                });
        });
    }

    /**
     * Accessors and Mutators
     */
    public function getFormattedBillingAddressAttribute()
    {
        if ($this->billing_address_id == $this->shipping_address_id) {
            return $this->formattedShippingAddress;
        } elseif ($this->billing_address_id) {
            return $this->billing_address->getFormatted($this->customer->fullName);
        }
    }

    public function getFormattedShippingAddressAttribute()
    {
        if ($this->shipping_address_id) {
            return $this->shipping_address->getFormatted($this->customer->fullName);
        }
    }

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
     * @param   Status      $status         The new status
     * @param   Driver      $driver         Driver making the status change
     * @param   integer     $user           Backend user making the status change
     */
    public function changeStatus(Status $status, Driver $driver = null, $user = null)
    {
        if ($this->status_id == $status->id) {
            return;
        }

        $this->status_id = $status->id;
        $this->status_at = Carbon::now();
        $this->save();

        $author = null;
        if (!is_null($driver)) {
            $author = $driver->name;
        } elseif (!is_null($user)) {
            $author = $user->fullName;
        }

        OrderEvent::create([
            'order_id'  => $this->id,
            'user_id'   => $user ? $user->id : null,
            'driver_id' => $driver ? $driver->id : null,
            'message'   => Lang::get('bedard.shop::lang.orders.status_changed', [
                'status' => '<strong>'.Lang::get($status->name).'</strong>',
                'author' => Lang::get($author),
            ]),
        ]);
    }

    /**
     * Returns the Cart model as it was at the time of sale
     *
     * @return  Bedard\Shop\Models\Cart
     */
    public function getCachedCart()
    {
        return (new CartCache)->build($this->cart_cache);
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
