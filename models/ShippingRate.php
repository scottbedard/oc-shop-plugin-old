<?php namespace Bedard\Shop\Models;

use Lang;
use Model;
use RainLab\Location\Models\State;

/**
 * ShippingRate Model
 */
class ShippingRate extends Model
{

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
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'method' => [
            'Bedard\Shop\Models\ShippingMethod',
        ],
    ];

    /**
     * Relationships
     */
    public $belongsToMany = [
        'countries' => [
            'RainLab\Location\Models\Country',
            'table' => 'bedard_shop_shipping_country_rate',
            'key'   => 'rate_id',
            'scope' => 'isEnabled',
        ],
        'states' => [
            'RainLab\Location\Models\State',
            'table' => 'bedard_shop_shipping_rate_state',
            'key'   => 'rate_id',
        ],
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
        $units  = Settings::getWeightUnits();
        $lang   = strtolower(Lang::get('bedard.shop::lang.common.weight_'.$units.'_singular'));
        $fields->rate->comment = Lang::get('bedard.shop::lang.shippingrates.rate_comment', ['units' => $lang]);
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

        if (!$countryIds)
            return [];

        $states = State::select('rainlab_location_states.*', 'rainlab_location_countries.code')
            ->join('rainlab_location_countries', 'rainlab_location_states.country_id', '=', 'rainlab_location_countries.id')
            ->whereIn('country_id', $countryIds)
            ->with('country')
            ->orderBy('rainlab_location_countries.code', 'asc')
            ->orderBy('rainlab_location_states.name', 'asc')
            ->get();

        $options = [];
        foreach ($states as $state) {
            $options[$state->id] = count($countryIds) <= 1
                ? $state->name
                : $state->country->code.': '.$state->name;
        }

        return $options;
    }
}
