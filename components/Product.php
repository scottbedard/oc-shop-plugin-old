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
            'inventory_script' => [
                'title'             => 'bedard.shop::lang.components.product.inventory_script',
                'description'       => 'bedard.shop::lang.components.product.inventory_script_info',
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
        $product = ProductModel::isActive()
            ->where('slug', $this->property('slug'))
            ->with('current_price.discount')
            ->with('options.values')
            ->with('inventories.values')
            ->with('images')
            ->first();

        // Return a 404 if the product doesn't exist
        if (!$product) {
            return $this->controller->run('404');
        }

        // Save the Product and alias it's properties for easier Twig access
        $this->product          = $product;
        $this->base_price       = $product->base_price;
        $this->description_html = $product->description_html;
        $this->inventories      = $product->inventories;
        $this->isDiscounted     = $product->isDiscounted;
        $this->isInStock        = $product->isInStock;
        $this->isOutOfStock     = $product->isOutOfStock;
        $this->name             = $product->name;
        $this->options          = $product->options;
        $this->price            = $product->price;
        $this->slug             = $product->slug;
        $this->snippet_html     = $product->snippet_html;
    }
}
