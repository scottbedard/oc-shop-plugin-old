<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Price Model
 */
class Price extends Model
{
    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_prices';

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
        'product_id',
        'discount_id',
        'price',
    ];

    /**
     * @var boolean Disable default timestamps
     */
    public $timestamps = false;

    /**
     * @var array   Relations
     */
    public $belongsTo = [
        'discount' => [
            'Bedard\Shop\Models\Discount',
        ],
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];

    /**
     * Query Scopes
     */
    public function scopeIsNotDiscounted($query)
    {
        return $query->whereNull('discount_id');
    }

    public function scopeIsDiscounted($query)
    {
        return $query->whereNotNull('discount_id');
    }

    /**
     * Ensure that a discount never creates a negative price
     *
     * @param   float   $value
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value > 0 ? $value : 0;
    }

    public function getPriceAttribute()
    {
        return isset($this->attributes['price'])
            ? floatval($this->attributes['price'])
            : 0;
    }

    /**
     * Recalculates a price
     */
    public function refresh()
    {
        // If there is no discount, just use the base_price
        if (!$this->discount) {
            $this->price = $this->product->base_price;
            return $this->save();
        }

        // Otherwise calculate the new price
        $discount = $this->discount->is_percentage
                ? $this->product->base_price * ($this->discount->amount_percentage / 100)
                : $this->discount->amount_exact;

        $this->price = $this->product->base_price - $discount;
        return $this->save();
    }

}
