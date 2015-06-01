<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Option;
use Bedard\Shop\Tests\Fixtures\Generate;
use DB;

class OptionModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_option_validation()
    {
        $this->setExpectedException('ValidationException');
        $option = Generate::option('');
    }

    public function test_options_require_one_value()
    {
        $option = Generate::option('Foo');
        $this->setExpectedException('ValidationException');
        $option->validateValues([]);
    }

    public function test_option_value_names_are_required()
    {
        $option = Generate::option('Size');
        $this->setExpectedException('ValidationException');
        $option->validateValues(['Small', 'Medium', '']);
    }

    public function test_option_values_must_be_unique()
    {
        $option = Generate::option('Color');
        $this->setExpectedException('ValidationException');
        $option->validateValues(['Red', 'Blue', 'Green', 'GREEN']);
    }

}
