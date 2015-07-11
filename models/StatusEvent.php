<?php namespace Bedard\Shop\Models;

use Carbon\Carbon;
use Model;

/**
 * StatusEvent Model
 */
class StatusEvent extends Model
{

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_status_events';

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
        'status_at',
    ];

    /**
     * @var boolean Disable default timestamps
     */
    public $timestamps = false;

    /**
     * @var boolean Date fields
     */
    protected $dates = ['status_at'];

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
        $this->status_at = Carbon::now();
    }

}
