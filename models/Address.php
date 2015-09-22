<?php namespace Bedard\Shop\Models;

use Model;
use Exception;
use Adamlc\AddressFormat\Format;

/**
 * Address Model
 */
class Address extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_addresses';

    /**
     * @var array   Implemented behaviors
     */
    public $implement = ['RainLab.Location.Behaviors.LocationModel'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'organization',
        'street_1',
        'street_2',
        'city',
        'postal_code',
        'state_name',
        'is_billing',
        'is_shipping',
    ];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'carts' => [
            'Bedard\Shop\Models\Cart',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'street_1'      => 'required',
        'city'          => 'required',
        'postal_code'   => 'required',
        'state_name'    => 'required_without:state_id',
        'state_id'      => 'required_without:state_name',
        'country_id'    => 'required',
    ];

    /**
     * Accessors and Mutators
     */
    public function getStateOrNameAttribute()
    {
        if (!$this->state) {
            return $this->attributes['state_name'] ?: false;
        }

        return $this->state->name;
    }

    public function getStateCodeOrNameAttribute()
    {
        if (!$this->state) {
            return $this->attributes['state_name'] ?: false;
        }

        return $this->state->code;
    }

    /**
     * Return the address in a localized format
     *
     * @param   string      $recipient
     * @return  string
     */
    public function getFormatted($recipient = null)
    {
        $formatter = new Format;
        try {
            $formatter->setLocale($this->country->code);
        } catch(Exception $e) {
            // todo: allow shops to specify their origin country,
            // and use that code here
            $formatter->setLocale('SP');
        }

        if ($recipient !== null) {
            $formatter['RECIPIENT'] = $recipient;
        }

        $formatter['ORGANIZATION']      = $this->organization;

        $formatter['STREET_ADDRESS'] = $this->street_2
            ? $this->street_1.', '.$this->street_2
            : $this->street_1;

        $formatter['LOCALITY']          = $this->city;
        $formatter['ADMIN_AREA']        = $this->stateCodeOrName;
        $formatter['POSTAL_CODE']       = $this->postal_code;
        $formatter['COUNTRY']           = $this->country->name;

        return $formatter->formatAddress(true).'<br />'.$this->country->name;
    }

}
