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
    public function getIsDiscountedAttribute()
    {
        return $this->baseSubtotal < $this->subtotal;
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
                'items.inventory.product.thumbnails',
                'items.inventory.values.option',
            ]);

            if ($this->promotion_id) {
                $this->load('promotion.products');
            }

            $this->isLoaded = true;
        }
    }
}
