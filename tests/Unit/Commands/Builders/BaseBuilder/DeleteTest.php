<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class DeleteTest extends TestSetup
{
    use Specify;

    public function testDelete()
    {
        $this->specify("it drops a single record dispatching from `delete()`", function () {
            $actual = $this->builder
                ->delete()
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_DELETE
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
