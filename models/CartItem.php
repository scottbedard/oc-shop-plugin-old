<?php namespace Bedard\Shop\Models;
;
use Model;

/**
 * CartItem Model
 */
class CartItem extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_cart_items';

    /**
     * @var array   Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array   Fillable fields
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'inventory_id',
        'quantity',
    ];

    /**
     * @var array   Timestamp fields
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array   Relations
     */
    public $belongsTo = [
        'cart' => [
            'Bedard\Shop\Models\Cart',
        ],
        'inventory' => [
            'Bedard\Shop\Models\Inventory',
        ],
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];

    /**
     * Accessors and Mutators
     */
    public function getBasePriceAttribute()
    {
        return $this->inventory->base_price;
    }

    public function getBaseSubtotalAttribute()
    {
        return $this->base_price * $this->quantity;
    }

    public function getIsDiscountedAttribute()
    {
        return $this->price < $this->base_price;
    }

    public function getNameAttribute()
    {
        return $this->inventory->product->name;
    }

    public function getOptionsAttribute()
    {
        return $this->inventory->values->lists('name', 'option.name');
    }

    public function getPriceAttribute()
    {
        return $this->inventory->price;
    }

    public function getSlugAttribute()
    {
        return $this->inventory->product->slug;
    }

    public function getSubtotalAttribute()
    {
        return $this->price * $this->quantity;
    }

    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = $value > 0 ? $value : 0;
    }
}
