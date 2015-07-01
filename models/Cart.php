<?php namespace Bedard\Shop\Models;

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
     * @var array Relations
     */
    public $belongsTo = [
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
    public function getHasPromotionProductsAttribute()
    {
        if (!$this->promotion) return false;

        $this->loadRelationships();
        $cart = $this->items->lists('product_id');
        $required = $this->promotion->products->lists('id');

        return empty($required) || (bool) array_intersect($cart, $required);
    }

    public function getIsDiscountedAttribute()
    {
        return $this->baseSubtotal < $this->subtotal;
    }

    public function getIsPromotionMinimumReachedAttribute()
    {
        if (!$this->promotion) return false;

        return $this->promotion->cart_minimum > 0
            ? $this->subtotal >= $this->promotion->cart_minimum
            : true;
    }

    public function getIsPromotionRunningAttribute()
    {
        return $this->promotion
            ? $this->promotion->isRunning
            : false;
    }

    public function getIsUsingPromotionAttribute()
    {
        return $this->promotion &&
            $this->isPromotionRunning &&
            $this->isPromotionMinimumReached &&
            $this->hasPromotionProducts;
    }

    public function getBaseSubtotalAttribute()
    {
        $this->loadRelationships();
        return $this->items->sum('baseSubtotal');
    }

    public function getSubtotalAttribute()
    {
        $this->loadRelationships();
        return $this->items->sum('subtotal');
    }

    /**
     * Lazy loads related models if they haven't already been loaded
     */
    public function loadRelationships()
    {
        if (!$this->isLoaded) {
            $this->load([
                'items.inventory.product.current_price',
                'items.inventory.values.option',
            ]);

            if ($this->promotion_id) {
                $this->load('promotion.products');
            }

            $this->isLoaded = true;
        }
    }
}
