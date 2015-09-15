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
                ->internalCreate($record)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target'=> Bag::ELEMENT_VERTEX,
                'data' => $record
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it inserts multiple records", function () {
            $records = [
                ['first' => 'first-value', 'A', 'a'],
                ['first' => 'second-value', 'B', 'b']
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'data' => $records,
                'target'=> Bag::ELEMENT_VERTEX,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testCreateEdges()
    {
        $this->specify("it inserts a single edge", function () {
            $record = [
                'first' => 'first-value',
                'second' => 'second-value',
                'inV' => 'a',
                'outV' => 'b',
            ];

            $actual = $this->builder
                ->internalCreate($record)
                ->type(Bag::ELEMENT_EDGE)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'target' => Bag::ELEMENT_EDGE,
                'data' => $record
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
