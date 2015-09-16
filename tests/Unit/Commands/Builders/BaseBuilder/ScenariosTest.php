<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\BaseBuilder;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class ScenariosTest extends TestSetup
{
    use Specify;

    public function testScenarios()
    {
        $this->specify("Create (C) two vertices and an edge between them", function () {
            $records = [
                [Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'what'],
                [Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'ever'],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                    Bag::ELEMENT_LABEL => 'friend',
                    Bag::EDGE_INV => (new BaseBuilder())->internalCreate([[Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'michael']]),
                    Bag::EDGE_OUTV => (new BaseBuilder())->internalCreate([[Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'dylan']])
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

        $this->specify("Find (R) existing vertices and create (C) an edge between them", function () {
            $records = [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                Bag::ELEMENT_LABEL => 'friend',
                Bag::EDGE_INV => (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND]),
                Bag::EDGE_OUTV => (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "dylan", Bag::CONJUNCTION_AND]),
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("Find (R) existing vertices and create (C) an edge between them with properties", function () {
            $records = [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                Bag::ELEMENT_LABEL => 'friend',
                Bag::EDGE_INV => (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND]),
                Bag::EDGE_OUTV => (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "dylan", Bag::CONJUNCTION_AND]),
                'weight' => 15
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("Find (R) existing vertices and create (C) an edge between them and update (U) that edge", function () {
            $records = [
                Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                Bag::ELEMENT_LABEL => 'friend',
                Bag::EDGE_INV => (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND]),
                Bag::EDGE_OUTV => (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "dylan", Bag::CONJUNCTION_AND]),
                'weight' => 15
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->internalWhere([
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_EDGE, Bag::CONJUNCTION_AND],
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'friend', Bag::CONJUNCTION_AND],
                    [Bag::EDGE_INV, Bag::COMPARATOR_EQUAL, (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND]), Bag::CONJUNCTION_AND],
                    [Bag::EDGE_OUTV, Bag::COMPARATOR_EQUAL, (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "dylan", Bag::CONJUNCTION_AND]), Bag::CONJUNCTION_AND],
                ])
                ->internalUpdate(['weight' => 15])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records,
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_EDGE, Bag::CONJUNCTION_AND],
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'friend', Bag::CONJUNCTION_AND],
                    [Bag::EDGE_INV, Bag::COMPARATOR_EQUAL, (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND]), Bag::CONJUNCTION_AND],
                    [Bag::EDGE_OUTV, Bag::COMPARATOR_EQUAL, (new BaseBuilder())->internalRetrieve()->internalWhere(["name", Bag::COMPARATOR_EQUAL, "dylan", Bag::CONJUNCTION_AND]), Bag::CONJUNCTION_AND],
                ],
                'update' => ['weight' => 15]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("Create (C) elements and update (U) them with data", function () {
            $records = [
                [Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'what'],
                [Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX, Bag::ELEMENT_LABEL => 'person', "name" => 'ever'],
            ];

            $actual = $this->builder
                ->internalCreate($records)
                ->internalWhere([
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'person', Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, 'dylan', Bag::CONJUNCTION_AND]
                ])
                ->internalUpdate(['a' => 'A'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => $records,
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'person', Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, 'dylan', Bag::CONJUNCTION_AND]
                ],
                'update' => ['a' => 'A']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it deletes vertices using `retrieve` and `delete`", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->internalWhere([
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, 'dylan', Bag::CONJUNCTION_AND]
                ])
                ->limit(1)
                ->internalDelete()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, 'dylan', Bag::CONJUNCTION_AND]
                ],
                'delete' => true,
                'limit' => 1
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
