<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Price Model
 */
class Price extends Model
{
    use \Bedard\Shop\Traits\DateActiveTrait;

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_prices';

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
    ];

    /**
     * @var boolean Disable auto-incrementing ID
     */
    public $incrementing = false;

    /**
     * @var boolean Disable default timestamps
     */
    public $timestamps = false;

    /**
     * @var array   Configure start and end dates
     */
    protected $dates = ['start_at', 'end_at'];

    /**
     * @var array   Relations
     */
    public $belongsTo = [
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];

    /**
     * Model Events
     */
    public function beforeSave()
    {
        // This exists to help sqlite handle a null price value
        $this->attributes['price'] = $this->price ?: 0;
    }

}
