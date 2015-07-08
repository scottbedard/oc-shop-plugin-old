<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Classes\WeightHelper;
use Bedard\Shop\Models\ShippingSettings;
use Model;

/**
 * Cart Model
 */
class Cart extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_carts';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'key',
    ];

    /**
     * @var array Attribute casting
     */
    protected $casts = [
        'shipping_failed' => 'boolean',
    ];

    /**
     * @var array Jsonable fields
     */
    protected $jsonable = ['shipping_rates'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'address' => [
            'Bedard\Shop\Models\Address',
        ],
        'customer' => [
            'Bedard\Shop\Models\Customer',
        ],
        'payment' => [
            'Bedard\Shop\Models\Payment',
        ],
        'promotion' => [
            'Bedard\Shop\Models\Promotion',
        ],
    ];
    public $hasMany = [
        'items' => [
            'Bedard\Shop\Models\CartItem',
        ],
    ];

    /**
     * @var boolean     Determines if relationships have been loaded or not, or reset
     */
    public $isLoaded = false;

    /**
     * Accessors and Mutators
     */
    public function getBaseSubtotalAttribute()
    {
        return $this->items->sum('baseSubtotal');
    }

    public function getHasAddressAttribute()
    {
        return $this->address_id != null;
    }

    public function getHasCustomerAttribute()
    {
        return $this->customer_id != null;
    }

    public function getHasPromotionAttribute()
    {
        return $this->promotion_id != null;
    }

    public function getHasPromotionProductsAttribute()
    {
        if (!$this->hasPromotion) return false;

        $cart = $this->items->lists('product_id');
        $required = $this->promotion->products->lists('id');

        return empty($required) || (bool) array_intersect($cart, $required);
    }

    public function getIsDiscountedAttribute()
    {
        return $this->baseSubtotal < $this->subtotal;
    }

    public function getIsEmptyAttribute()
    {
        return $this->items->sum('quantity') == 0;
    }

    public function getIsPromotionMinimumReachedAttribute()
    {
        if (!$this->hasPromotion) return false;

        return $this->promotion->cart_minimum > 0
            ? $this->subtotal >= $this->promotion->cart_minimum
            : true;
    }

    public function getIsPromotionRunningAttribute()
    {
        return $this->hasPromotion
            ? $this->promotion->isRunning
            : false;
    }

    public function getIsUsingPromotionAttribute()
    {
        return $this->hasPromotion &&
            $this->isPromotionRunning &&
            $this->isPromotionMinimumReached &&
            $this->hasPromotionProducts;
    }

    public function getSubtotalAttribute()
    {
        return $this->items->sum('subtotal');
    }

    public function getTotalAttribute()
    {
        return $this->subtotal - $this->promotionSavings;
    }

    public function getPromotionSavingsAttribute()
    {
        if (!$this->isUsingPromotion) return 0;

        $savings = $this->promotion->is_cart_percentage
                ? round($this->subtotal * ($this->promotion->cart_percentage / 100), 2)
                : $this->promotion->cart_exact;

        if ($savings > $this->subtotal)
            $savings = $this->subtotal;

        return $savings;
    }

    public function getShippingIsRequiredAttribute()
    {
        // If we have no calculator, or we already have rates
        if (!ShippingSettings::getCalculator() || count($this->shipping_rates) > 0) {
            return false;
        }

        // Return true if shipping is required, or it has not failed
        return !$this->shipping_failed || ShippingSettings::getIsRequired();
    }

    public function getWeightAttribute()
    {
        return $this->items->sum('weight');
    }

    /**
     * Handle before checkout events
     */
    public function beforeCheckout()
    {
        echo 'hey';
    }

    /**
     * Return the cart weight in a specified unit
     *
     * @param   string  $unit   The desired return unit
     * @return  float
     */
    public function getWeight($unit = 'oz')
    {
        return WeightHelper::convert($this->items->sum('weight'), $unit);
    }
}
