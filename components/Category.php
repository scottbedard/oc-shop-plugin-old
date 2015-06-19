<?php namespace Bedard\Shop\Components;

use Bedard\Shop\Models\Category as CategoryModel;
use Cms\Classes\ComponentBase;
use Lang;
use Response;

class Category extends ComponentBase
{

    /**
     * Component details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'          => 'bedard.shop::lang.components.category.name',
            'description'   => 'bedard.shop::lang.components.category.description'
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
                'title'             => 'bedard.shop::lang.components.category.slug',
                'description'       => 'bedard.shop::lang.components.category.slug_description',
                'default'           => '{{ :slug }}',
                'type'              => 'dropdown',
            ],
            'default' => [
                'title'             => 'bedard.shop::lang.components.category.default',
                'description'       => 'bedard.shop::lang.components.category.default_description',
                'default'           => '{{ :slug }}',
                'type'              => 'dropdown',
            ],
            'page' => [
                'title'             => 'bedard.shop::lang.components.category.page',
                'description'       => 'bedard.shop::lang.components.category.page_description',
                'default'           => '{{ :page }}',
                'type'              => 'string',
                'validationPattern' => '^[1-9][0-9]*$',
                'validationMessage' => Lang::get('bedard.shop::lang.components.category.page_invalid'),
            ],
            'notfound' => [
                'title'             => 'bedard.shop::lang.components.category.notfound',
                'description'       => 'bedard.shop::lang.components.category.notfound_description',
                'type'              => 'checkbox',
                'default'           => true,
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Loads all categories and their slug
     */
    public function getSlugOptions()
    {
        return CategoryModel::orderBy('name', 'asc')->lists('name', 'slug');
    }

    /**
     * Run the component
     */
    public function onRun()
    {
        // Load the requested category, and return if it's not found
        if (!$category = CategoryModel::where('slug', $this->property('slug'))->first()) {
            return $this->property('notfound')
                ? Response::make($this->controller->run('404'), 404)
                : false;
        }

        // Save the category and alias it's properties for easier Twig access
        $this->category     = $category;
        $this->name         = $category->name;
        $this->slug         = $category->slug;
        $this->rows         = $category->rows;
        $this->columns      = $category->columns;
        $this->sort_key     = $category->sort_key;
        $this->sort_order   = $category->sort_order;
        $this->filter       = $category->filter;
        $this->filter_value = $category->filter_value;

        // Load the products
        $this->products = $category->getProducts(1);
    }
}
