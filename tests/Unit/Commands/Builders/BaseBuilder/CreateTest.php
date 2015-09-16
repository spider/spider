<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class CreateTest extends TestSetup
{
    use Specify;

    /* Create Tests */
    public function testCreateVertices()
    {
        $this->specify("it inserts a single vertex", function () {
            $records = [
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'first' => 'first-value',
                    'A' =>  'a'
                ]
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it inserts multiple vertices", function () {
            $records = [
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'first' => 'first-value',
                    'A' =>  'a'
                ],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'second' => 'second-value',
                    'B' =>  'b'
                ],
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testCreateEdges()
    {
        $this->specify("it inserts a single edge", function () {
            $records = [
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                    Bag::ELEMENT_LABEL => 'test',
                    Bag::EDGE_INV => 'in-id',
                    Bag::EDGE_OUTV => 'out-id',
                    'first' => 'first-value',
                    'A' =>  'a'
                ]
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it inserts multiple edges", function () {
            $records = [
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                    Bag::ELEMENT_LABEL => 'test',
                    Bag::EDGE_INV => 'in-id',
                    Bag::EDGE_OUTV => 'out-id',
                    'first' => 'first-value',
                    'A' =>  'a'
                ],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                    Bag::ELEMENT_LABEL => 'test',
                    Bag::EDGE_INV => 'in-id',
                    Bag::EDGE_OUTV => 'out-id',
                    'second' => 'second-value',
                    'B' =>  'b'
                ],
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records
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
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                    Bag::ELEMENT_LABEL => 'label',
                    Bag::EDGE_INV => 'a',
                    Bag::EDGE_OUTV => 'b',
                ]
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
