<?php namespace Bedard\Shop\Models;

use Flash;
use Lang;
use Model;
use October\Rain\Exception\ValidationException;

/**
 * Inventory Model
 */
class Inventory extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_inventories';

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
    public $belongsTo = [
        'product' => [
            'Bedard\Shop\Models\Product',
        ],
    ];
    public $belongsToMany = [
        'values' => [
            'Bedard\Shop\Models\Value',
            'table' => 'bedard_shop_inventory_value',
        ],
    ];

    /**
     * Validation
     */
    public $rules = [
        'product_id'    => 'required',
        'sku'           => 'unique:bedard_shop_inventories',
        'quantity'      => 'integer|min:0',
        'modifier'      => 'numeric',
    ];

    /**
     * Query Scopes
     */
    public function scopeFindByValues($query, $values = [])
    {
        // Selects an inventory by it's associated values
        return $query->has('values', '=', count($values))
            ->where(function($inventory) use ($values) {
                foreach ($values as $id) {
                    $inventory->whereHas('values', function($value) use ($id) {
                        $value->where('id', $id);
                    });
                }
            })
            ->first();
    }

    /**
     * Accessors and Mutators
     */
    public function getBasePriceAttribute()
    {
        return $this->product->base_price + $this->modifier;
    }

    public function getPriceAttribute()
    {
        return $this->product->price + $this->modifier;
    }

    public function setSkuAttribute($value)
    {
        // Prevent duplicate SKUs by converting empty strings to null
        $this->attributes['sku'] = $value ?: null;
    }

    /**
     * Filter form fields
     */
    public function filterFields($fields, $context = null)
    {
        $fields->values->hidden = $this->product->options->count() == 0;
    }

    /**
     * Returns related value names in the correct order
     *
     * @return  array
     */
    public function getValueNames()
    {
        $values = [];
        foreach ($this->values as $value) {
            $values[$value->option->position] = $value->name;
        }

        ksort($values);

        return $values;
    }

    /**
     * Run validation to make sure the inventory is unique, and
     * save it with it's related values.
     *
     * @param   array   $valueIds
     */
    public function saveWithValues($valueIds = [])
    {
        $this->validate();

        $valueIds = is_array($valueIds)
            ? array_filter($valueIds)
            : [];

        $inventory = Inventory::where('id', '<>', intval($this->id))
            ->where('product_id', $this->product_id)
            ->has('values', '=', count($valueIds));

        foreach ($valueIds as $valueId) {
            $inventory->whereHas('values', function($value) use ($valueId) {
                $value->where('id', $valueId);
            });
        }

        $exists = $inventory->count();
        if ($exists > 0) {
            $message = Lang::get('bedard.shop::lang.inventories.inventory_exists');
            Flash::error($message);
            throw new ValidationException($message);
        }

        $this->save();
        $this->values()->sync($valueIds);
    }
}
