<?php namespace Bedard\Shop\Tests\Functional\Models;

use Bedard\Shop\Models\Status;
use Bedard\Shop\Tests\Fixtures\Generate;

class StatusModelTest extends \OctoberPluginTestCase
{

    protected $refreshPlugins = ['Bedard.Shop'];

    public function test_core_statuses_cannot_be_deleted()
    {
        $core   = Status::create(['name' => 'Core', 'core_status' => 'yup']);
        $custom = Status::create(['name' => 'Custom']);

        $this->assertEquals(2, Status::whereIn('id', [$core->id, $custom->id])->count());
        $this->assertFalse($core->delete());
        $this->assertTrue($custom->delete());
    }
}
