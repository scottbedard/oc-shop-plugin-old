<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Promotion Model
 */
class Promotion extends Model
{
    use \Bedard\Shop\Traits\DateActiveTrait;

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_promotions';

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
     * @var array   Relations
     */
    public $belongsToMany = [
        'products' => [
            'Bedard\Shop\Models\Product',
            'table' => 'bedard_shop_product_promotion',
            'order' => 'name asc',
        ],
    ];

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
