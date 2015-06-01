<?php namespace Bedard\Shop\Models;

use Lang;
use Model;
use Flash;
use October\Rain\Exception\ValidationException;

/**
 * Option Model
 */
class Option extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_options';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'values' => [
            'Bedard\Shop\Models\Value',
            'order' => 'position asc',
        ],
    ];
    public $belongsTo = [
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'name' => 'required',
    ];

    public function validateValues(array $values)
    {
        // Values are validated here to make sure they all pass
        // before anything is created or updated in the database.
        $this->validate();
        $values = array_map('strtolower', $values);

        if (count($values) == 0) {
            $message = Lang::get('bedard.shop::lang.options.values_required');
            Flash::error($message);
            throw new ValidationException($message);
        }

        if (in_array('', $values)) {
            $message = Lang::get('bedard.shop::lang.values.name_required');
            Flash::error($message);
            throw new ValidationException($message);
        }

        if (count($values) != count(array_unique($values))) {
            $message = Lang::get('bedard.shop::lang.values.name_unique');
            Flash::error($message);
            throw new ValidationException($message);
        }
    }

    /**
     * Model Events
     */
    public function afterDelete()
    {
        // todo: delete associated inventories
    }

}
