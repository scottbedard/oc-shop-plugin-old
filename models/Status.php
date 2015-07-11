<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Status Model
 */
class Status extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_statuses';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'icon',
        'class',
    ];

    /**
     * @var array Date fields
     */
    protected $dates = ['status_at'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'orders' => [
            'Bedard\Shop\Models\Order',
        ],
    ];

    /**
     * Query Scopes
     */
    public function scopeGetCore($query, $status)
    {
        return $query->where('core_status', $status)->first();
    }

}
