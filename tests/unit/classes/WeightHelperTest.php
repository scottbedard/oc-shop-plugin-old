<?php namespace Bedard\Shop\Tests\Functional\Classes;

use Bedard\Shop\Classes\WeightHelper;

class WeightHelperTest extends \OctoberPluginTestCase
{
    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_unit_conversion()
    {
        $this->assertEquals(1, WeightHelper::convert(1, 'oz', 'oz'));
        $this->assertEquals(0.0625, WeightHelper::convert(1, 'lb', 'oz'));
        $this->assertEquals(0.0283, WeightHelper::convert(1, 'kg', 'oz'));
        $this->assertEquals(28.3495, WeightHelper::convert(1, 'gr', 'oz'));

        $this->assertEquals(16, WeightHelper::convert(1, 'oz', 'lb'));
        $this->assertEquals(1, WeightHelper::convert(1, 'lb', 'lb'));
        $this->assertEquals(0.4536, WeightHelper::convert(1, 'kg', 'lb'));
        $this->assertEquals(453.592, WeightHelper::convert(1, 'gr', 'lb'));

        $this->assertEquals(0.0353, WeightHelper::convert(1, 'oz', 'gr'));
        $this->assertEquals(0.0022, WeightHelper::convert(1, 'lb', 'gr'));
        $this->assertEquals(0.001, WeightHelper::convert(1, 'kg', 'gr'));
        $this->assertEquals(1, WeightHelper::convert(1, 'gr', 'gr'));

        $this->assertEquals(35.274, WeightHelper::convert(1, 'oz', 'kg'));
        $this->assertEquals(2.2046, WeightHelper::convert(1, 'lb', 'kg'));
        $this->assertEquals(1, WeightHelper::convert(1, 'kg', 'kg'));
        $this->assertEquals(1000, WeightHelper::convert(1, 'gr', 'kg'));
    }
}
