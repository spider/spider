<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class CreateTest extends TestSetup
{
    use Specify;

    /* Create Tests */
    public function testCreateRecords()
    {
        $this->specify("it inserts a single record", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value'
            ];

            $actual = $this->builder
                ->type(Bag::ELEMENT_VERTEX)
                ->insert($record)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target' => Bag::ELEMENT_VERTEX,
                'data' => $record,
                'createCount' => 1
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it inserts multiple records", function () {
            $records = [
                ['first' => 'first-value', 'A', 'a'],
                ['first' => 'second-value', 'B', 'b']
            ];

            $actual = $this->builder
                ->type(Bag::ELEMENT_VERTEX)
                ->insert($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target' => Bag::ELEMENT_VERTEX,
                'data' => $records,
                'createCount' => 2
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
