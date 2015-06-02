<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Option;
use Bedard\Shop\Tests\Fixtures\Generate;
use DB;

class OptionModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    /**
     * Product options should be unique
     */
    public function test_option_names_are_unique()
    {
        $this->setExpectedException('ValidationException');
        $option1 = Generate::option('Foo', ['product_id' => 1]);
        $option2 = Generate::option('FOO', ['product_id' => 1]);
    }

    /**
     * Test various Value validation
     */
    public function test_options_require_one_value()
    {
        $option = Generate::option('Foo');
        $this->setExpectedException('ValidationException');
        $option->saveWithValues([], []);
    }

    public function test_option_value_names_are_required()
    {
        $option = Generate::option('Size');
        $this->setExpectedException('ValidationException');
        $option->saveWithValues([0,0,0], ['Small', 'Medium', '']);
    }

    public function test_option_values_must_be_unique()
    {
        $option = Generate::option('Color');
        $this->setExpectedException('ValidationException');
        $option->saveWithValues([0,0,0,0], ['Red', 'Blue', 'Green', 'GREEN']);
    }

    /**
     * Create an option, and test adding / updating / deleting values
     */
    public function test_managing_value_relationship()
    {
        $option = Generate::option('Bar');
        $option->saveWithValues([0,0], ['Hello', 'World']);
        $option->load('values');

        $hello = $option->values->where('name', 'Hello')->first();
        $world = $option->values->where('name', 'World')->first();

        $this->assertTrue((bool) $hello);
        $this->assertTrue((bool) $world);

        $option->saveWithValues([$hello->id], ['Foo']);
        $option->load('values');

        $foo = $option->values->where('name', 'Foo')->first();
        $world = $option->values->where('name', 'World')->first();

        $this->assertTrue((bool) $foo);
        $this->assertFalse((bool) $world);
    }

}
