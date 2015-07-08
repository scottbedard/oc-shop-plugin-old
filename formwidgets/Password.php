<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * Password Form Widget
 */
class Password extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bedard_shop_password';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('password');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
    }

    /**
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {
        return $value;
    }

}
