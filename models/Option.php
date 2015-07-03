<?php namespace Bedard\Shop\Models;

use Bedard\Shop\Models\Product;
use Bedard\Shop\Models\Value;
use Flash;
use Lang;
use Model;
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

    /**
     * Model Events
     */
    public function afterValidate()
    {
        if ($this->product_id) {
            $names = Option::where('product_id', $this->product_id)
                ->where('id', '<>', $this->id ?: 0)
                ->lists('name');
        } else {
            $sessionKey = input('sessionKey') ?: $this->sessionKey;
            $names = Product::findOrNew($this->product_id)
                ->options()
                ->withDeferred($sessionKey)
                ->lists('name', 'id');

            if (isset($names[$this->id])) {
                unset($names[$this->id]);
            }
        }

        if (in_array(strtolower($this->name), array_map('strtolower', $names))) {
            $message = Lang::get('bedard.shop::lang.options.name_unique');
            Flash::error($message);
            throw new ValidationException($message);
        }
    }

    public function beforeCreate()
    {
        // Prevent options from having the same position
        $this->position = Option::where('product_id', $this->product_id)->count() + 1;
    }

    public function afterDelete()
    {
        // Delete associated values
        foreach ($this->values as $value) {
            $value->delete();
        }

        // Keep the positions updated
        $options = Option::where('product_id', $this->product_id)
            ->orderBy('position', 'asc')
            ->get();

        $position = 1;
        foreach ($options as $option) {
            if ($option->position != $position) {
                $option->position = $position;
                $option->save();
            }
            $position++;
        }
    }

    /**
     * Run some simple validation, then save the Option model with it's
     * related Value models.
     *
     * @param   array | null    $ids
     * @param   array | null    $names
     */
    public function saveWithValues($ids, $names)
    {
        $error = false;
        if (!$ids || !$names)
            $error = Lang::get('bedard.shop::lang.options.values_required');
        elseif (count($names) != count(array_unique(array_map('strtolower', $names))))
            $error = Lang::get('bedard.shop::lang.values.name_unique');
        elseif (in_array('', $names))
            $error = Lang::get('bedard.shop::lang.values.name_required');

        if ($error) {
            Flash::error($error);
            throw new ValidationException($error);
        }

        $this->save();

        // Create / update values
        $saveIds = $ids;
        foreach ($ids as $i => $id) {
            $value = Value::findOrNew($ids[$i]);
            $value->option_id   = $this->id;
            $value->name        = $names[$i];
            $value->position    = $i + 1;
            $value->save();

            $savedIds[] = $value->id;
        }

        // Delete values
        foreach ($this->values as $value) {
            if (!in_array($value->id, $savedIds)) {
                $value->delete();
            }
        }
    }

}
