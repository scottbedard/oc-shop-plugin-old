<?php namespace Bedard\Shop\Models;

use Backend\Models\ImportModel;
use Bedard\Shop\Models\Category;
use Bedard\Shop\Models\Product;
use ApplicationException;
use Exception;

/**
 * Product import model
 */
class ProductImport extends ImportModel
{
    /**
     * Validation rules
     */
    public $rules = [
        'name'          => 'required',
        'base_price'    => 'numeric|min:0'
    ];

    protected $categoryNameCache = [];

    public function getProductCategoriesOptions()
    {
        return Category::lists('name', 'id');
    }

    public function importData($results, $sessionKey = null)
    {
        $firstRow = reset($results);

        /*
         * Validation
         */
        if ($this->auto_create_categories && !array_key_exists('categories', $firstRow)) {
            throw new ApplicationException('Please specify a match for the Categories column.');
        }

        foreach ($results as $row => $data) {
            try  {
                if (!$name = array_get($data, 'name')) {
                    $this->logSkipped($row, 'Missing product name');
                    continue;
                }

                /*
                 * Find or create
                 */
                $product = Product::make();

                if ($this->update_existing) {
                    $product = $this->findDuplicateProduct($data) ?: $product;
                }

                $productExists = $product->exists;

                /*
                 * Set attributes
                 */
                $except = ['categories'];

                foreach (array_except($data, $except) as $attribute => $value) {
                    $product->{$attribute} = $value ?: null;
                }

                $product->forceSave();

                if ($categoryIds = $this->getCategoryIdsForProduct($data)) {
                    $product->categories()->sync($categoryIds);
                }

                /*
                 * Log results
                 */
                if ($productExists) {
                    $this->logUpdated();
                } else {
                    $this->logCreated();
                }
            } catch (Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }

    protected function findDuplicateProduct($data)
    {
        $name = array_get($data, 'name');
        $product = Product::where('name', $name);

        if ($slug = array_get($data, 'slug')) {
            $product->orWhere('slug', $slug);
        }

        return $product->first();
    }


    protected function getCategoryIdsForProduct($data)
    {
        $ids = [];

        if ($this->auto_create_categories) {
            $categoryNames = $this->decodeArrayValue(array_get($data, 'categories'));

            foreach ($categoryNames as $name) {
                if (!$name = trim($name)) {
                    continue;
                }

                if (isset($this->categoryNameCache[$name])) {
                    $ids[] = $this->categoryNameCache[$name];
                } else {
                    $newCategory = Category::firstOrCreate(['name' => $name]);
                    $ids[] = $this->categoryNameCache[$name] = $newCategory->id;
                }
            }
        } elseif ($this->product_categories) {
            $ids = (array) $this->product_categories;
        }

        return $ids;
    }

}