<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\OrderEvent;
use DB;
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
    protected $fillable = [];

    /**
     * Jsonable fields
     */
    protected $jsonable = ['cart_cache'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'events' => [
            'Bedard\Shop\Models\OrderEvent',
            'order' => 'created_at desc',
        ],
    ];

    /**
     * Model Events
     */
    public function afterCreate()
    {
        $this->changeStatus(1);
    }

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
     * Accessors and Mutators
     */
    public function getStatusAttribute()
    {
        $current = $this->events->first();
        return $current ? $current->status : false;
    }

    /**
     * Change the order's status
     *
     * @param   integer     $status_id      The new status
     * @param   integer     $user_id        The backend user making the change
     */
    public function changeStatus($status_id, $user_id = null)
    {
        if (($current = $this->events->first()) && $current->status_id == $status_id) {
            return;
        }

        OrderEvent::create([
            'order_id'  => $this->id,
            'status_id' => $status_id,
            'user_id'   => $user_id,
        ]);
    }

}
