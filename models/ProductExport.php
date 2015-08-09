<?php namespace Bedard\Shop\Models;

use Backend\Models\ExportModel;

/**
 * Product export model
 */
class ProductExport extends ExportModel
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'bedard_shop_products';

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'product_categories' => [
            'Bedard\Shop\Models\Category',
            'table'    => 'bedard_shop_category_product',
            'key'      => 'product_id',
            'otherKey' => 'category_id'
        ]
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['categories'];

    public function exportData($columns, $sessionKey = null)
    {
        $result = self::make()
            ->with(['product_categories'])
            ->get()
            ->toArray();

        return $result;
    }

    public function getCategoriesAttribute()
    {
        if (!$this->product_categories) {
            return '';
        }

        return $this->encodeArrayValue($this->product_categories->lists('name'));
    }

}