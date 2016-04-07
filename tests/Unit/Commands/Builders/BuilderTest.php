<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\Builder;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class BuilderTest extends TestSetup
{
    use Specify;

    public function setup()
    {
        $this->builder = new Builder();
    }

    public function testSelectAndTarget()
    {
        $this->specify("it returns specified data using a SELECT projections array", function () {
            $actual = $this->builder
                ->select(['price', 'certified'])
                ->record("#12:6767")// byId() alias
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => ['price', 'certified'],
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, "#12:6767", Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns records using `from()`", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, "V", Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testType()
    {
        $this->specify("it sets type from 'vertex'", function () {
            $actual = $this->builder
                ->select()
                ->type('vertex')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it normalizes 'vERteX", function () {
            $actual = $this->builder
                ->select()
                ->type('vERteX')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it sets type from 'edge'", function () {
            $actual = $this->builder
                ->select()
                ->type('edge')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_EDGE, Bag::CONJUNCTION_AND],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it normalizes 'eDGe", function () {
            $actual = $this->builder
                ->select()
                ->type('eDGe')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_EDGE, Bag::CONJUNCTION_AND],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it passes a constant through", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_EDGE)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_EDGE, Bag::CONJUNCTION_AND],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testWhereSugars()
    {
        // First test is duplicated in BaseBuilder on purpose.
        $this->specify("it adds a single, full where constraint", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_VERTEX)
                ->where(['name', Bag::COMPARATOR_EQUAL, 'michael', Bag::CONJUNCTION_OR])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_OR],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it filters by a single where equals constraint", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_VERTEX)
                ->where('certified', 'yes')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, 'yes', Bag::CONJUNCTION_AND],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag: true");
        });

        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_VERTEX)
                ->where('name', 'michael')
                ->where('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_VERTEX)
                ->where(['name', '=', 'michael'])
                ->where('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_VERTEX)
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2]
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of WHERE OR constraints", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_VERTEX)
                ->where('certified', true)
                ->where(['name', '=', 'michael', 'OR'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_OR],

                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds an array of an array of WHERE AND constraints", function () {
            $actual = $this->builder
                ->select()
                ->type(Bag::ELEMENT_VERTEX)
                ->where([
                    ['name', '=', 'michael'],
                    ['price', '>', 2, 'OR']
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_OR]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testAndOrWheres()
    {
        $this->specify("it adds several AND WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->andWhere('last', 'wilson')
                ->andWhere('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, "V", Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_AND],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds several OR WHERE constraints", function () {
            $actual = $this->builder
                ->select()
                ->from("V")
                ->where('name', 'michael')
                ->orWhere('last', 'wilson')
                ->orWhere('certified', true)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, "V", Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['last', Bag::COMPARATOR_EQUAL, "wilson", Bag::CONJUNCTION_OR],
                    ['certified', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_OR],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testLimitSugars()
    {
        $this->specify("it gets one record", function () {
            $actual = $this->builder
                ->select()
                ->from('v')
                ->one()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, "v", Bag::CONJUNCTION_AND],
                ],
                'limit' => 1
            ]);

            $this->assertEquals($expected, $actual, 'failed to return correct command');
        });
    }

    public function testInsert()
    {
        /* ToDo: Figure out API Builder sugar for inserting records */
        // This test is copied from BaseBuilderTest::testCreate()#4
        $this->specify("it adds the `insert` alias", function () {
            $actual = $this->builder
                ->insert([
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'first' => 'first-value',
                    'A' =>  'a'
                ])
                ->insert([
                    [
                        Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                        Bag::ELEMENT_LABEL => 'test',
                        'second' => 'second-value',
                        'B' =>  'b'
                    ],
                    [
                        Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                        Bag::ELEMENT_LABEL => 'test',
                        'third' => 'third-value',
                        'C' =>  'c'
                    ]
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => [
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
                    [
                        Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                        Bag::ELEMENT_LABEL => 'test',
                        'third' => 'third-value',
                        'C' =>  'c'
                    ]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testUpdate()
    {
        $this->specify("it adds to update by property and value", function () {
            $actual = $this->builder
                ->update('property', 'value')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'update' => ['property' => 'value'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds to update by an array of data", function () {
            $actual = $this->builder
                ->update([
                    'a' => 'A',
                    'b' => 'B'
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'update' => [
                    'a' => 'A',
                    'b' => 'B'
                ],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds to update by property and value through method chaining", function () {
            $actual = $this->builder
                ->update('property', 'value')
                ->update('b', 'B')
                ->update([
                    'c' => 'C',
                    'd' => 'D'
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'update' => [
                    'property' => 'value',
                    'b' => 'B',
                    'c' => 'C',
                    'd' => 'D'
                ],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

    }

    public function testDelete()
    {
        $this->specify("it drops a single record dispatching from `delete()`", function () {
            $actual = $this->builder
                ->delete(3)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, 3, Bag::CONJUNCTION_AND]],
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it drops multiple records dispatching from `delete()`", function () {
            $actual = $this->builder
                ->delete([1, 2, 3])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_IN, [1, 2, 3], Bag::CONJUNCTION_AND]],
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it drops multiple records via constraints", function () {
            $actual = $this->builder
                ->delete()
                ->from('target')
                ->where('birthday', 'apr')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_LABEL, Bag::COMPARATOR_EQUAL, 'target', Bag::CONJUNCTION_AND],['birthday', Bag::COMPARATOR_EQUAL, 'apr', Bag::CONJUNCTION_AND]],
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testByRecords() {
        $this->specify("it adds a single record id via `record()`", function () {
            $actual = $this->builder
                ->record(3)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_EQUAL, 3, Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records via ids", function () {
            $actual = $this->builder
                ->records([1, 2, 3])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'where' => [[Bag::ELEMENT_ID, Bag::COMPARATOR_IN, [1, 2, 3], Bag::CONJUNCTION_AND]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
