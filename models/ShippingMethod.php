<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Classes\WeightHelper;
use Lang;
use Model;

/**
 * ShippingMethod Model
 */
class ShippingMethod extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string  The database table used by the model.
     */
    public $table = 'bedard_shop_shipping_methods';

    /**
     * @var array   Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array   Fillable fields
     */
    protected $fillable = [
        'name',
        'min_weight',
        'max_weight',
    ];

    /**
     * @var array   Relations
     */
    public $hasMany = [
        'rates' => [
            'Bedard\Shop\Models\ShippingRate',
        ],
    ];

    /**
     * @var array   Validation
     */
    public $rules = [
        'name'          => 'required',
        'min_weight'    => 'numeric|min:0',
        'max_weight'    => 'numeric|min:0',
    ];

    /**
     * Accessors and Mutators
     */
    public function getMaxWeightAttribute()
    {
        return array_key_exists('max_weight', $this->attributes) && $this->attributes['max_weight'] > 0
            ? $this->attributes['max_weight']
            : null;
    }

    public function getMinWeightAttribute()
    {
        return array_key_exists('min_weight', $this->attributes) && $this->attributes['min_weight'] > 0
            ? $this->attributes['min_weight']
            : null;
    }

    public function setMaxWeightAttribute($value)
    {
        $this->attributes['max_weight'] = $value ?: 0;
    }

    public function setMinWeightAttribute($value)
    {
        $this->attributes['min_weight'] = $value ?: 0;
    }

    /**
     * Filter the form fields
     */
    public function filterFields($fields, $context = null)
    {
        $units = WeightHelper::getPlural();
        $fields->min_weight->comment = Lang::get('bedard.shop::lang.shippingmethods.min_weight_comment', ['units' => $units]);
        $fields->max_weight->comment = Lang::get('bedard.shop::lang.shippingmethods.max_weight_comment', ['units' => $units]);
    }
}
