<?php namespace Bedard\Shop\Models;

use Backend;
use Lang;
use Model;

/**
 * Driver Model
 */
class Driver extends Model
{
    use \October\Rain\Database\Traits\Encryptable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_drivers';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'name',
        'class',
        'type',
        'config',
        'is_enabled',
    ];

    /**
     * Jsonable fields
     */
    protected $jsonable = ['config'];

    /**
     * Encryptable fields
     */
    protected $encryptable = ['config'];


    /**
     * Relationships
     */
    public $attachOne = [
        'image' => ['System\Models\File'],
    ];

    /**
     * Query Scopes
     */
    public function scopeIsConfigured($query)
    {
        return $query->where(function($query) {
            $query->whereNotNull('config')->orWhere('is_configurable', false);
        });
    }

    public function scopeIsPayment($query)
    {
        return $query->where('type', 'payment');
    }

    public function scopeIsShipping($query)
    {
        return $query->where('type', 'shipping');
    }

    /**
     * Accessors and Mutators
     */
    public function getIsConfiguredAttribute()
    {
        return !$this->is_configurable || (bool) $this->config;
    }

    public function getNameAttribute()
    {
        return isset($this->attributes['name'])
            ? Lang::get($this->attributes['name'])
            : null;
    }

    /**
     * Returns an instance of the driver class
     *
     * @return  object
     */
    public function getClass()
    {
        return new $this->class;
    }

    /**
     * Returns a value from the config array
     *
     * @param   string      $key
     * @return  mixed
     */
    public function getConfig($key)
    {
        return is_array($this->config) && array_key_exists($key, $this->config)
            ? $this->config[$key]
            : null;
    }

}
