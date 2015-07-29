<?php
namespace Spider\Test\Unit\Commands\Builders\Builder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\Builder\TestSetup;

class CreateTest extends TestSetup
{
    use Specify;

    /* Create Tests */
    public function testCreateRecords()
    {
        $this->specify("it uses into and insert aliases", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value'
            ];

            $actual = $this->builder
                ->into('target')
                ->insert($record)
                ->getCommandBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target' => "target",
                'data' => $record,
                'createCount' => 1
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
