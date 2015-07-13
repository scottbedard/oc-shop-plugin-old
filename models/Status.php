<?php namespace Bedard\Shop\Models;

use Flash;
use Lang;
use Model;

/**
 * Status Model
 */
class Status extends Model
{
    use \October\Rain\Database\Traits\Validation;

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
        'core_status',
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'orders' => [
            'Bedard\Shop\Models\Order',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'name' => 'required',
    ];

    /**
     * Model Events
     */
    public function beforeDelete()
    {
        if (!is_null($this->core_status)) {
            Flash::error(Lang::get('bedard.shop::lang.statuses.protected_status'));
            return false;
        }
    }

    /**
     * Query Scopes
     */
    public function scopeGetCore($query, $status)
    {
        return $query->where('core_status', $status)->first();
    }

}
