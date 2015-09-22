<?php namespace Bedard\Shop\Components;

use Cms\Classes\Page;
use Bedard\Shop\Models\Category as CategoryModel;
use Cms\Classes\ComponentBase;

class Navigation extends ComponentBase
{

    /**
     * A collection of categories to display.
     *
     * @var Collection
     */
    public $categories;

    /**
     * Reference to the page name for linking to categories.
     *
     * @var string
     */
    public $categoryPage;


    /**
     * Component details
     *
     * @return  array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'bedard.shop::lang.components.navigation.name',
            'description' => 'bedard.shop::lang.components.navigation.description',
        ];
    }

    /**
     * Defines the properties used by this class.
     */
    public function defineProperties()
    {
        return [
            'categoryPage' => [
                'title'       => 'Category page',
                'description' => 'Name of the category page file.',
                'type'        => 'dropdown'
            ]
        ];
    }

    public function getCategoryPageOptions() {
        return Page::withComponent('shopCategory')->sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Run the component
     */
    public function onRun()
    {
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->categories = $this->page['categories'] = $this->loadCategories();
    }

    protected function loadCategories()
    {
        $categories = CategoryModel::make()->getAllRoot()->where('is_hidden', 0);
        return $this->listCategories($categories);
    }

    protected function listCategories($categories)
    {
        return $categories->each(function($category) {
            $category->setUrl($this->categoryPage, $this->controller);

            if ($category->children) {
                $this->listCategories($category->children);
            }
        });
    }

}
