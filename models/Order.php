<?php namespace Bedard\Shop\Models;

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
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

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

}
