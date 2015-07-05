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
     * Query Scopes
     */
    public function scopeWhereWeight($query, $weight)
    {
        return $query
            ->where(function($range) use ($weight) {
                $range
                    ->where(function($lower) use ($weight) {
                        $lower
                            ->whereNull('min_weight')
                            ->orWhere('min_weight', '<=', $weight);
                    })
                    ->where(function($upper) use ($weight) {
                        $upper
                            ->whereNull('max_weight')
                            ->orWhere('max_weight', '>=', $weight);
                    });
            });
    }

    /**
     * Accessors and Mutators
     */
    public function setMaxWeightAttribute($value)
    {
        $this->attributes['max_weight'] = $value ?: null;
    }

    public function setMinWeightAttribute($value)
    {
        $this->attributes['min_weight'] = $value ?: null;
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
