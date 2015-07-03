<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Bedard\Shop\Models\Product;

/**
 * ValueSelector Form Widget
 */
class ValueSelector extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bedard_shop_value_selector';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('valueselector');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->model->load('product.options.values');
        $this->vars['inventory'] = $this->model;

        $sessionKey = input('sessionKey') ?: $this->sessionKey;
        if ($this->model->product_id) {
            $this->vars['options'] = $this->model->product->options()->withDeferred($sessionKey)->get();
        } else {
            $product = new Product;
            $this->vars['options'] = $product->options()->withDeferred($sessionKey)->get();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return $value;
    }

}
