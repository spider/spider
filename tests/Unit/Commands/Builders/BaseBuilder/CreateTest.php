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
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testCreateVerticesAndEdges()
    {
        $this->specify("it inserts a two vertices and an edge", function () {
            $records = [
                [Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'what'],
                [Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'ever'],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'label',
                    Bag::EDGE_INV => 'a',
                    Bag::EDGE_OUTV => 'b',
                ]

            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'command' => Bag::COMMAND_CREATE,
                'data' => $records
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
