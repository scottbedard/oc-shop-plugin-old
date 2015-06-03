<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;

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
        $this->vars['options'] = $this->model->product->options;
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return $value;
    }

}
