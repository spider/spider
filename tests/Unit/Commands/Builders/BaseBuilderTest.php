<?php
namespace Spider\Test\Unit\Commands\Builders\BaseBuilder;

use Codeception\Specify;
use Spider\Commands\Bag;
use Spider\Commands\BaseBuilder;
use Spider\Test\Stubs\CommandProcessorStub;
use Spider\Test\Unit\Commands\Builders\TestSetup;

class BaseBuilderTest extends TestSetup
{
    use Specify;

    public function setup()
    {
        $this->builder = new BaseBuilder();
    }

    /* Operation Tests */
    public function testCreate()
    {
        $this->specify("it adds a single record to the create", function () {
            $actual = $this->builder
                ->internalCreate([
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'first' => 'first-value',
                    'A' =>  'a'
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'create' => [[
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'first' => 'first-value',
                    'A' =>  'a'
                ]]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records to the create", function () {
            $actual = $this->builder
                ->internalCreate([
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
                    ]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records through method chaining", function () {
            $actual = $this->builder
                ->internalCreate([
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'first' => 'first-value',
                    'A' =>  'a'
                ])
                ->internalCreate([
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'second' => 'second-value',
                    'B' =>  'b'
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
                    ]
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple records in a variety of ways", function () {
            $actual = $this->builder
                ->internalCreate([
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'test',
                    'first' => 'first-value',
                    'A' =>  'a'
                ])
                ->internalCreate([
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

    public function testUpdate(){
        $this->specify("it sets properties to update", function () {
            $actual = $this->builder
                ->internalUpdate([
                    'name' => 'chris',
                    'birthday' => 'april'
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'update' => ['name' => 'chris', 'birthday' => 'april'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds properties to update through method chaining", function () {
            $actual = $this->builder
                ->internalUpdate(['a' => 'A'])
                ->internalUpdate(['b' => 'B'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'update' => ['a' => 'A', 'b' => 'B'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testDelete()
    {
        $this->specify("it sets the delete flag to true", function () {
            $actual = $this->builder
                ->internalDelete()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'delete' => true,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testProjections()
    {
        /* Also thoroughly tests csvToArray() */
        $this->specify("it returns nothing by default", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => []
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns a single value", function () {
            $actual = $this->builder
                ->internalRetrieve('username')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => ['username']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from array", function () {
            $actual = $this->builder
                ->internalRetrieve(['username', 'password'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it several properties from csv string (one space)", function () {
            $actual = $this->builder
                ->internalRetrieve('username, password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (no spaces)", function () {
            $actual = $this->builder
                ->internalRetrieve('username,password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it returns several properties from csv string (many spaces)", function () {
            $actual = $this->builder
                ->internalRetrieve('username,           password')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => ['username', 'password']
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it throws exception if projections is not array or string", function () {
            $this->builder
                ->internalRetrieve()
                ->internalRetrieve(3)
                ->getBag();

        }, ['throws' => new \InvalidArgumentException("Projections must be a comma-separated string or an array")]);
    }

    public function testConstraints()
    {
        $this->specify("it adds a single array of a valid constraint", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->internalWhere(['name', Bag::COMPARATOR_EQUAL, 'michael', Bag::CONJUNCTION_OR])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_OR],
                ],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it adds multiple valid constraint", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->internalWhere([
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, 'michael', Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_OR]
                ])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                    ['price', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_OR],
                ]
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it throws an exception for invalid constraint: too few parameters", function () {
            $this->builder
                ->internalRetrieve()
                ->internalWhere([
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', Bag::COMPARATOR_EQUAL, 'michael']
                ])
                ->getBag();
        }, ['throws' => 'Exception']);

        $this->specify("it throws an exception for invalid constraint: operator not a constant", function () {
            $this->builder
                ->internalRetrieve()
                ->internalWhere([
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    ['name', '=', 'michael', Bag::CONJUNCTION_AND]
                ])
                ->getBag();
        }, ['throws' => 'Exception']);

        $this->specify("it throws an exception for invalid constraint: conjunction not a constant", function () {
            $this->builder
                ->internalRetrieve()
                ->internalWhere(
                    [true, Bag::COMPARATOR_EQUAL, 'michael', 'AND']
                )
                ->getBag();
        }, ['throws' => 'Exception']);
    }

    public function testLimit()
    {
        $this->specify("it adds a specified limit", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->limit(2)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'limit' => 2,
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testGroupBy()
    {
        $this->specify("it groups results by a single field", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->groupBy('certified')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'groupBy' => ['certified'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields array", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->groupBy(['certified', 'price'])
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'groupBy' => ['certified', 'price'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it groups results by a multiple fields string", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->groupBy('certified, price')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'groupBy' => ['certified', 'price'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    public function testOrderBy()
    {
        $this->specify("it orders results by a field, asc by default", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->orderBy('price')
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'orderBy' => [['price', Bag::ORDER_ASC]],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, desc", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->orderBy('price', Bag::ORDER_DESC)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'orderBy' => [['price', Bag::ORDER_DESC]],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("it orders results by a field, asc", function () {
            $actual = $this->builder
                ->internalRetrieve()
                ->orderBy('price', Bag::ORDER_ASC)
                ->getBag();

            $expected = $this->buildExpectedBag([
                'retrieve' => [],
                'orderBy' => [['price', Bag::ORDER_ASC]],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    /* Response Format Tests */
    public function testResponseFormats()
    {
        // Does not dispatch, only flags the `Bag`

        $this->specify("sets response format as tree", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->mapAsTree()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'map' => Bag::MAP_TREE
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });

        $this->specify("sets response format as path", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->mapAsPath()
                ->getBag();

            $expected = $this->buildExpectedBag([
                'map' => Bag::MAP_PATH
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }

    /* Bag Management Tests */
    public function testClearBag()
    {
        $this->builder
            ->internalRetrieve();

        $this->builder->clear();

        $this->assertEquals(new Bag(), $this->builder->getBag(), "failed to return an empty bag");
    }

    /* Integration Tests */
    public function testGetScript()
    {
        $this->specify("sets optional language processors", function () {
            $this->builder->setProcessor(new CommandProcessorStub());

            $actual = $this->builder
                ->internalRetrieve('something')
                ->getScript();

            $expected = $this->buildExpectedCommand([
                'retrieve' => ['something'],
            ]);

            $this->assertEquals($expected, $actual, "failed to return correct command bag");
        });
    }
}
