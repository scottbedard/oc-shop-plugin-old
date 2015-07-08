<?php namespace Bedard\Shop\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Bedard\Shop\Models\Driver;
use Illuminate\Support\Facades\Validator;
use Lang;
use October\Rain\Database\Model;
use October\Rain\Exception\AjaxException;

/**
 * DriverSettings Form Widget
 */
class DriverSettings extends FormWidgetBase
{

    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'bedard_shop_driver_settings';

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('widget');
    }

    /**
     * Prepares the form widget view data
     */
    public function prepareVars()
    {
        $scope = $this->config->scope;
        $this->vars['drivers'] = Driver::$scope()->with('image')->get();
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('css/driversettings.css', 'Bedard.Shop');
        $this->addJs('js/driversettings.js', 'Bedard.Shop');
    }

    /**
     * Create a popup form to edit a driver's configuration
     *
     * @return  mixed
     */
    public function onPopup()
    {
        $driver     = Driver::find(input('id'));
        $class      = $driver->getClass();
        $fields     = $class->registerFields();
        $tabFields  = $class->registerTabFields();

        $model = new Model;
        foreach (array_merge(array_keys($fields), array_keys($tabFields)) as $key) {
            $model->$key = $driver->getConfig($key);
        }

        $form = $this->makeConfigFromArray([
            'fields' => $fields,
            'tabs' => ['fields' => $tabFields],
        ]);

        $form->model = $model;

        return $this->makePartial('popup', [
            'driver'    => $driver,
            'form'      => $this->makeWidget('Backend\Widgets\Form', $form),
        ]);
    }

    /**
     * Update a driver's configuration
     */
    public function onUpdateDriver()
    {
        if (!$driver = Driver::find(input('_id'))) {
            throw new AjaxException(Lang::get('bedard.shop::lang.settings.payment.driver_not_found'));
        }

        $config = input();
        unset($config['_id']);

        if (empty($config)) {
            return;
        }

        // Reset password fields matching an api password token
        if ($tokens = input('_api_password_tokens')) {
            foreach ($tokens as $key => $value) {
                if (isset($config[$key]) && $config[$key] == $value) {
                    $config[$key] = $driver->getConfig($key);
                }
            }
        }

        // Validate the input
        $class = $driver->getClass();
        if ($class->rules) {
            $validator = Validator::make($config, $class->rules, $class->getCustomMessages());
            if (!$validator->passes()) {
                throw new AjaxException($validator->messages()->first());
            }
        }

        $driver->config = $config;
        $driver->save();
    }

}
