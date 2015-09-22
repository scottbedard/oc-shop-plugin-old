<?php namespace Bedard\Shop\Models;

use Lang;
use Model;
use RainLab\Location\Models\State;
use Bedard\Shop\Classes\WeightHelper;

/**
 * ShippingRate Model
 */
class ShippingRate extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_shipping_rates';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'base_price',
        'rate',
        'countries',
        'states',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'method' => [
            'Bedard\Shop\Models\ShippingMethod',
            'key' => 'shipping_method_id',
        ],
    ];

    public $belongsToMany = [
        'countries' => [
            'RainLab\Location\Models\Country',
            'table' => 'bedard_shop_shipping_country_rate',
            'scope' => 'isEnabled',
        ],
        'states' => [
            'RainLab\Location\Models\State',
            'table' => 'bedard_shop_shipping_rate_state',
        ],
    ];

    /**
     * Query Scopes
     */
    public function scopeCountry($query, $id)
    {
        return $query->whereHas('countries', function($country) use ($id) {
            $country->where('id', $id);
        });
    }

    public function scopeForCart($query, Cart $cart)
    {
        return $query
            ->weight($cart->weight)
            ->country($cart->shipping_address->country_id)
            ->state($cart->shipping_address->state_id);
    }

    public function scopeState($query, $id)
    {
        if (!$id) {
            return $query;
        }

        return $query->where(function($states) use ($id) {
            $states
                ->whereHas('states', function($state) use ($id) {
                    $state->where('id', $id);
                })
                ->orHas('states', '=', 0);
        });
    }

    public function scopeWeight($query, $weight)
    {
        return $query->whereHas('method', function($method) use ($weight) {
            $method->weight($weight);
        });
    }

    /**
     * Validation
     */
    public $rules = [
        'base_price'    => 'numeric|min:0',
        'rate'          => 'numeric|min:0',
    ];

    /**
     * Accessors and Mutators
     */
    public function setBasePriceAttribute($value)
    {
        $this->attributes['base_price'] = $value ?: 0;
    }

    public function setRateAttribute($value)
    {
        $this->attributes['rate'] = $value ?: 0;
    }

    /**
     * Filter the form fields
     */
    public function filterFields($fields, $context = null)
    {
        $fields->rate->comment = Lang::get('bedard.shop::lang.shippingrates.rate_comment', [
            'units' => WeightHelper::getSingular()
        ]);
    }

    /**
     * Returns state options based on the selected countries
     *
     * @return  array
     */
    public function getStatesOptions()
    {
        $countryIds = isset(input('ShippingRate')['countries'])
            ? input('ShippingRate')['countries']
            : $this->countries->lists('id');

        if (!$countryIds) {
            return [];
        }

        $states = State::select('rainlab_location_states.*', 'rainlab_location_countries.code')
            ->join('rainlab_location_countries', 'rainlab_location_states.country_id', '=', 'rainlab_location_countries.id')
            ->whereIn('country_id', $countryIds)
            ->with('country')
            ->orderBy('rainlab_location_countries.code', 'asc')
            ->orderBy('rainlab_location_states.name', 'asc')
            ->get();

        $options = [];
        foreach ($states as $state) {
            $options[$state->id] = count($countryIds) > 1
                ? $state->country->code.': '.$state->name
                : $state->name;
        }

        return $options;
    }
}
