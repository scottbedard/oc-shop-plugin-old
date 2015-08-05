<?php namespace Bedard\Shop\Components;

use Bedard\Shop\Models\Product as ProductModel;
use Cms\Classes\ComponentBase;

class Product extends ComponentBase
{

    /**
     * @var ProductModel        The product being displayed
     */
    public $product;

    /**
     * Component details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'bedard.shop::lang.components.product.name',
            'description' => 'bedard.shop::lang.components.product.description'
        ];
    }

    /**
     * Define properties
     *
     * @return  array
     */
    public function defineProperties()
    {
        return [
            'slug' => [
                'title'             => 'bedard.shop::lang.components.product.slug',
                'description'       => 'bedard.shop::lang.components.product.slug_description',
                'default'           => '{{ :slug }}',
                'type'              => 'dropdown',
            ],
            'use_selector' => [
                'title'             => 'bedard.shop::lang.components.product.use_selector',
                'description'       => 'bedard.shop::lang.components.product.use_selector_info',
                'type'              => 'checkbox',
                'type'              => 'checkbox',
                'default'           => true,
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Return a list of possible slugs
     *
     * @return  array
     */
    public function getSlugOptions()
    {
        return ProductModel::orderBy('name', 'asc')->lists('name', 'slug');
    }

    /**
     * Run the component
     */
    public function onRun()
    {
        // Load the selected product
        $this->product = ProductModel::isEnabled()
            ->joinPrices()
            ->where('slug', $this->property('slug'))
            ->with('options.values')
            ->with('inventories.values')
            ->with('images')
            ->first();

        // Return a 404 if the product doesn't exist
        if (!$this->product) {
            return $this->controller->run('404');
        }

        // Include the inventory selection script
        if ($this->property('use_selector')) {
            $this->addJs('assets/js/inventory-selector.min.js');
        }
    }

    /**
     * Returns arrays of values corresponding to in-stock inventories
     *
     * @return  array
     */
    public function getAvailableInventories()
    {
        $inventories = [];
        foreach ($this->product->inventories as $inventory) {
            if ($inventory->quantity > 0) {
                $inventories[] = $inventory->values->lists('id');
            }
        }

        return $inventories;
    }
}
