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

}
