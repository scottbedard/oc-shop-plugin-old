<?php namespace Bedard\Shop\Models;

use Carbon\Carbon;
use Model;

/**
 * OrderEvent Model
 */
class OrderEvent extends Model
{

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_order_events';

    /**
     * @var array   Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array   Fillable fields
     */
    protected $fillable = [
        'order_id',
        'status_id',
        'user_id',
        'created_at',
    ];

    /**
     * @var boolean Disable default timestamps
     */
    public $timestamps = false;

    /**
     * @var boolean Date fields
     */
    protected $dates = ['created_at'];

    /**
     * @var array   Relations
     */
    public $belongsTo = [
        'order' => [
            'Bedard\Shop\Models\Order',
        ],
        'status' => [
            'Bedard\Shop\Models\Status',
        ],
    ];

    /**
     * Model Events
     */
    public function beforeCreate()
    {
        $this->created_at = Carbon::now();
    }

}
