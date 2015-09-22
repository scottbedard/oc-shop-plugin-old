<?php namespace Bedard\Shop\Models;

use Model;
use Bedard\Shop\Classes\WeightHelper;

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
     * @var array   Cacheable fields
     */
    public $cacheable = [
        'id',
        'customer_id',
        'shipping_address_id',
        'billing_address_id',
        'promotion_id',
        'shipping_rates',
        'shipping_id',
        'shipping_failed',
        'is_inventoried',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array   Attribute casting
     */
    public $casts = [
        'is_inventoried' => 'boolean',
    ];

    /**
     * @var array Jsonable fields
     */
    protected $jsonable = ['shipping_rates'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'billing_address' => [
            'Bedard\Shop\Models\Address',
            'key' => 'billing_address_id',
        ],
        'customer' => [
            'Bedard\Shop\Models\Customer',
        ],
        'promotion' => [
            'Bedard\Shop\Models\Promotion',
        ],
        'shipping_address' => [
            'Bedard\Shop\Models\Address',
            'key' => 'shipping_address_id',
        ],
    ];

    public $hasMany = [
        'items' => [
            'Bedard\Shop\Models\CartItem',
        ],
    ];

    public $hasOne = [
        'order' => [
            'Bedard\Shop\Models\Order',
        ],
    ];

    /**
     * @var boolean     Determines if relationships have been loaded or not, or reset
     */
    public $isLoaded = false;

    /**
     * Query Scopes
     */
    public function scopeIsCanceled($query)
    {
        return $query->where('status', 'canceled');
    }

    public function scopeIsComplete($query)
    {
        return $query->where('status', 'complete');
    }

    public function scopeIsOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeIsPaying($query)
    {
        return $query->where('status', 'paying');
    }

    public function scopeIsOpenOrCanceled($query)
    {
        return $query->where(function($query) {
            $query->where('status', 'open')->orWhere('status', 'canceled');
        });
    }

    /**
     * Accessors and Mutators
     */
    public function getBaseSubtotalAttribute()
    {
        return $this->items->sum('baseSubtotal');
    }

    public function getHasAddressesAttribute()
    {
        return $this->shipping_address_id != null && $this->billing_address_id != null;
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

    public function getIsUsingShippingPromotionAttribute()
    {
        if (!$this->isUsingPromotion || !$this->shipping_address_id || !$this->shipping_address) {
            return false;
        }

        $countries = $this->promotion->countries->lists('id');

        return !$countries || in_array($this->shipping_address->country_id, $countries);
    }

    public function getSubtotalAttribute()
    {
        return $this->items->where('deleted_at', null)->sum('subtotal');
    }

    public function getTotalAttribute()
    {
        return $this->subtotal - $this->promotionSavings + $this->shipping_cost;
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

    public function getShippingCostAttribute()
    {
        return $this->getSelectedShipping('cost') ?: 0;
    }

    public function getWeightAttribute()
    {
        return $this->items->sum('weight');
    }

    /**
     * Return a value from the selected shipping rate
     *
     * @param   string|null     $property
     * @return  mixed
     */
    public function getSelectedShipping($property = null)
    {
        // todo: write a test for this
        if ($this->shipping_id && is_array($this->shipping_rates)) {
            foreach ($this->shipping_rates as $rate) {
                if ($rate['id'] == $this->shipping_id) {
                    return !is_null($property)
                        ? $rate[$property]
                        : $rate;
                }
            }
        }

        return false;
    }

    /**
     * Returns the driver used to calculate the selected shipping
     *
     * @return  Driver
     */
    public function getSelectedShippingDriver()
    {
        return Driver::where('class', $this->getSelectedShipping('class'))->first();
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

    /**
     * Validate and potentially repair the CartItem quantities
     *
     * @return  boolean
     */
    public function validateQuantities()
    {
        $valid = true;
        foreach ($this->items as $item) {
            if (!$item->validateQuantity()) {
                $valid = false;
            }
        }

        return $valid;
    }
}
