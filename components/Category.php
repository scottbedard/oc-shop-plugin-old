<?php namespace Bedard\Shop\Components;

use Bedard\Shop\Models\Category as CategoryModel;
use Cms\Classes\ComponentBase;
use Lang;

class Category extends ComponentBase
{

    /**
     * @var CategoryModel   The category being viewed
     */
    public $category;

    /**
     * @var Collection      The category's products
     */
    public $products;

    /**
     * @var array           Pagination data
     */
    public $page = [];

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
                'type'              => 'dropdown',
                'showExternalParam' => false,
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
            'thumbnails' => [
                'group'             => 'bedard.shop::lang.components.category.eager_loading',
                'title'             => 'bedard.shop::lang.components.category.thumbnails',
                'description'       => 'bedard.shop::lang.components.category.thumbnails_description',
                'type'              => 'checkbox',
                'default'           => true,
                'showExternalParam' => false,
            ],
            'gallery' => [
                'group'             => 'bedard.shop::lang.components.category.eager_loading',
                'title'             => 'bedard.shop::lang.components.category.gallery',
                'description'       => 'bedard.shop::lang.components.category.gallery_description',
                'type'              => 'checkbox',
                'default'           => false,
                'showExternalParam' => false,
            ],
            'inventories' => [
                'group'             => 'bedard.shop::lang.components.category.eager_loading',
                'title'             => 'bedard.shop::lang.components.category.inventories',
                'description'       => 'bedard.shop::lang.components.category.inventories_description',
                'type'              => 'checkbox',
                'default'           => true,
                'showExternalParam' => false,
            ],
        ];
    }

    /**
     * Loads the available categories
     *
     * @return  array
     */
    public function getCategorySlugs()
    {
        return CategoryModel::orderBy('name', 'asc')->lists('name', 'slug');
    }

    public function getDefaultOptions()
    {
        return $this->getCategorySlugs();
    }

    public function getSlugOptions()
    {
        return $this->getCategorySlugs();
    }

    /**
     * Run the component
     */
    public function onRun()
    {
        // Query the selected category
        $slug = $this->property('slug') ?: $this->property('default');
        if (!$category = CategoryModel::where('slug', $slug)->first()) {
            return $this->property('notfound')
                ? $this->controller->run('404')
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

        // Load the pagination
        $this->loadPagination();

        // Load the products
        $relationships = [];
        if ($this->property('gallery')) $relationships[] = 'images';
        if ($this->property('thumbnails')) $relationships[] = 'thumbnails';
        if ($this->property('inventories')) $relationships[] = 'inventories';

        $this->products = $category->getProducts($this->page['current'], $relationships);

        // If the category isn't paginated, we can use one less query and just count the results
        if ($this->category->rows == 0) {
            $this->totalProducts = $this->products->count();
        }
    }

    /**
     * Loads a category's pagination data
     */
    protected function loadPagination()
    {
        // Non-paginated categories
        if ($this->category->rows == 0) {
            $this->page = [
                'first'     => 1,
                'last'      => 1,
                'current'   => 1,
                'next'      => false,
                'previous'  => false,
            ];
        }

        // Paginated categories
        else {
            $this->totalProducts = $this->category->countProducts();

            $perPage    = $this->category->rows * $this->category->columns;
            $last       = ceil($this->totalProducts / $perPage);
            $current    = intval($this->property('page'));
            if ($current > $last) $current = $last;
            if ($current < 1) $current = 1;

            $this->page = [
                'first'     => 1,
                'last'      => ceil($this->totalProducts / $perPage),
                'current'   => $current,
                'next'      => $current < $last ? $current + 1 : false,
                'previous'  => $current > 1 ? $current - 1 : false,
            ];
        }
    }
}
