<?php namespace Bedard\Shop\Models;

use Model;

/**
 * Customer Model
 */
class Customer extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_customers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'carts' => [
            'Bedard\Shop\Models\Cart',
        ],
        'orders' => [
            'Bedard\Shop\Models\Order',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'first_name'    => 'required',
        'last_name'     => 'required',
        'email'         => 'required|email',
    ];

    /**
     * Query Scops
     */
    public function selectAverageOrder($query)
    {

    }

    /**
     * Accessors and Mutators
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = ucfirst($value);
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = ucfirst($value);
    }
}
