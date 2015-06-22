<?php namespace Bedard\Shop\Components;

use Bedard\Shop\Models\Category as CategoryModel;
use Cms\Classes\ComponentBase;

class Navigation extends ComponentBase
{

    /**
     * @var Collection
     */
    public $categories;

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
     * Run the component
     */
    public function onRun()
    {
        $this->categories = CategoryModel::make()
            ->getAllRoot()
            ->where('is_hidden', 0);
    }

}
