<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Promotion Model
 */
class Promotion extends Model
{
    use \Bedard\Shop\Traits\NumericColumnTrait,
        \October\Rain\Database\Traits\Validation;

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_promotions';

    /**
     * @var array   Implemented behaviors
     */
    public $implement = ['Bedard.Shop.Behaviors.TimeSensitiveModel'];

    /**
     * @var array   Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array   Fillable fields
     */
    protected $fillable = [
        'cart_exact',
        'cart_percentage',
        'is_cart_percentage',
        'shipping_exact',
        'shipping_percentage',
        'is_shipping_percentage',
    ];

    /**
     * @var array   Cacheable fields
     */
    public $cacheable = [
        'id',
        'code',
        'message',
        'cart_exact',
        'cart_percentage',
        'cart_is_percentage',
        'shipping_exact',
        'shipping_percentage',
        'shipping_is_percentage',
        'cart_minimum',
        'start_at',
        'end_at',
    ];

    /**
     * @var array   Relations
     */
    public $belongsToMany = [
        'countries' => [
            'RainLab\Location\Models\Country',
            'table' => 'bedard_shop_country_promotion',
            'scope' => 'isEnabled',
        ],
        'products' => [
            'Bedard\Shop\Models\Product',
            'table' => 'bedard_shop_product_promotion',
            'order' => 'name asc',
        ],
    ];

    public $hasMany = [
        'carts' => [
            'Bedard\Shop\Models\Cart',
            // todo: 'scope' => 'isComplete'
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'code'                  => 'required|unique:bedard_shop_promotions',
        'cart_exact'            => 'numeric|min:0',
        'cart_percentage'       => 'integer|min:0|max:100',
        'shipping_exact'        => 'numeric|min:0',
        'shipping_percentage'   => 'integer|min:0|max:100',
        'cart_minimum'          => 'numeric|min:0',
    ];

    public $customMessages = [
        'end_at.after'      => 'bedard.shop::lang.common.end_at_invalid',
    ];

    public function scopeSelectCartAmount($query)
    {
        return $query->addSelect($this->selectNumeric(
            $this->table,
            'is_cart_percentage',
            'cart_percentage',
            'cart_exact',
            'cart_amount'
        ));
    }

    public function scopeSelectShippingAmount($query)
    {
        return $query->addSelect($this->selectNumeric(
            $this->table,
            'is_shipping_percentage',
            'shipping_percentage',
            'shipping_exact',
            'shipping_amount'
        ));
    }

    /**
     * Accessors and Mutators
     */
    public function setCartExactAttribute($value)
    {
        $this->attributes['cart_exact'] = $value ?: 0;
    }

    public function setShippingExactAttribute($value)
    {
        $this->attributes['shipping_exact'] = $value ?: 0;
    }

    public function setCartPercentageAttribute($value)
    {
        $this->attributes['cart_percentage'] = $value ?: 0;
    }

    public function setShippingPercentageAttribute($value)
    {
        $this->attributes['shipping_percentage'] = $value ?: 0;
    }

    public function setCartMinimumAttribute($value)
    {
        $this->attributes['cart_minimum'] = $value ?: 0;
    }

    /**
     * Filter form fields
     */
    public function filterFields($fields, $context = null)
    {
        $fields->cart_exact->hidden = $this->is_cart_percentage;
        $fields->cart_percentage->hidden = !$this->is_cart_percentage;
        $fields->shipping_exact->hidden = $this->is_shipping_percentage;
        $fields->shipping_percentage->hidden = !$this->is_shipping_percentage;
    }
}
