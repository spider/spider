<?php
namespace Spider\Test\Unit\Commands\Languages;

use Codeception\Specify;
use Spider\Commands\Bag;

/**
 * This is the base tests for all language processors.
 *
 * Each processor test should extend this class and implement the required methods.
 * This ensures that all processors meet the same testing requirements.
 *
 * See the existing processor tests for more information.
 */
abstract class BaseTestSuite extends \PHPUnit_Framework_TestCase
{
    use Specify;

    /* Begin Tests */
    /* Simple Tests */
//    public function testUpdate()
//    {
//        $this->specify("it processes a simple update bag", function () {
//
//            $bag = new Bag();
//            $bag->command = Bag::COMMAND_UPDATE;
//            $bag->target = Bag::ELEMENT_VERTEX;
//            $bag->data = [$this->getData()];
//            $bag->where = [[
//                Bag::ELEMENT_ID,
//                Bag::COMPARATOR_EQUAL, // convert to constant
//                'target_id',
//                Bag::CONJUNCTION_AND // convert to constant
//            ]];
//
//            $expected = $this->getExpectedCommand('update-simple');
//
//            $actual = $this->processor()->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });
//
//        $this->specify("it processes a complex update bag", function () {
//
//            $bag = new Bag();
//            $bag->command = Bag::COMMAND_UPDATE;
//            $bag->target = Bag::ELEMENT_VERTEX; // don't forget about TargetID
//            $bag->data = [$this->getData()];
//            $bag->where = array_merge($this->getWheres(), [[
//                Bag::ELEMENT_LABEL,
//                Bag::COMPARATOR_EQUAL, // convert to constant
//                'target',
//                Bag::CONJUNCTION_AND // convert to constant
//            ]]);
//            $bag->limit = 10;
//
//            $expected = $this->getExpectedCommand('update-complex');
//
//            $actual = $this->processor()->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });
//    }
//
//    public function testDelete()
//    {
//        $this->specify("it processes a simple delete bag", function () {
//
//            $bag = new Bag();
//            $bag->command = Bag::COMMAND_DELETE;
//            $bag->target = Bag::ELEMENT_VERTEX;
//            //$bag->data = $this->getData();
//            $bag->where = [[
//                Bag::ELEMENT_ID,
//                Bag::COMPARATOR_EQUAL, // convert to constant
//                'target_id',
//                Bag::CONJUNCTION_AND // convert to constant
//            ]];
//
//            $expected = $this->getExpectedCommand('delete-simple');
//
//            $actual = $this->processor()->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });
//
//        $this->specify("it processes a complex delete bag", function () {
//
//            $bag = new Bag();
//            $bag->command = Bag::COMMAND_DELETE;
//            $bag->target = Bag::ELEMENT_VERTEX;
//            $bag->where = array_merge($this->getWheres(), [[
//                Bag::ELEMENT_LABEL,
//                Bag::COMPARATOR_EQUAL, // convert to constant
//                'target',
//                Bag::CONJUNCTION_AND // convert to constant
//            ]]);
//            $bag->limit = 10;
//
//            $expected = $this->getExpectedCommand('delete-complex');
//
//            $actual = $this->processor()->process($bag);
//            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
//        });
//    }
//
    public function testSelect()
    {
        $this->specify("(R) by a single where constraint and no label", function () {
            $bag = new Bag([
                'retrieve' => [],
                'where' => [
                    ['name', Bag::COMPARATOR_EQUAL, "josh", Bag::CONJUNCTION_AND],
                ],
            ]);

            $expected = $this->getExpectedCommand('select-no-label-one-constraint');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });

        $this->specify("(R) by type and label", function () {
            $bag = new Bag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
                    [
                        Bag::ELEMENT_LABEL,
                        Bag::COMPARATOR_EQUAL, // convert to constant
                        'target',
                        Bag::CONJUNCTION_AND // convert to constant
                    ]
                ],
            ]);

            $expected = $this->getExpectedCommand('select-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("(R) EDGE by label, type, and single where", function () {
            $bag = new Bag([
                'retrieve' => [],
                'where' => [
                    [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_EDGE, Bag::CONJUNCTION_AND],
                    [
                        Bag::ELEMENT_LABEL,
                        Bag::COMPARATOR_EQUAL, // convert to constant
                        'target',
                        Bag::CONJUNCTION_AND // convert to constant
                    ]
                ],
            ]);

            $expected = $this->getExpectedCommand('select-simple-edge');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        // @todo do the same as above for both (requires traversals for neo

        $this->specify("(R) by label, type, and multiple wheres", function () {
            $bag = new Bag([
                'retrieve' => [],
            ]);

            $bag->where = array_merge($this->getWheres(), [
                [
                    Bag::ELEMENT_LABEL,
                    Bag::COMPARATOR_EQUAL, // convert to constant
                    'target',
                    Bag::CONJUNCTION_AND // convert to constant
                ],
                [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
            ]);

            $expected = $this->getExpectedCommand('select-constraints');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("(R) projections by label, type, and wheres - orders and limits", function () {
            $bag = new Bag([
                'retrieve' => ['field1', 'field2'],
            ]);

            $bag->where = array_merge($this->getWheres(), [
                [
                    Bag::ELEMENT_LABEL,
                    Bag::COMPARATOR_EQUAL, // convert to constant
                    'target',
                    Bag::CONJUNCTION_AND // convert to constant
                ],
                [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
            ]);
            $bag->limit = 3;
            $bag->orderBy = [['field1', Bag::ORDER_DESC]];

            $expected = $this->getExpectedCommand('select-order-by');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });

        $this->specify("(R) by label, type, and wheres - groups and limits", function () {
            $bag = new Bag([
                'retrieve' => [],
            ]);

            $bag->where = array_merge($this->getWheres(), [
                [
                    Bag::ELEMENT_LABEL,
                    Bag::COMPARATOR_EQUAL, // convert to constant
                    'target',
                    Bag::CONJUNCTION_AND // convert to constant
                ],
                [Bag::ELEMENT_TYPE, Bag::COMPARATOR_EQUAL, Bag::ELEMENT_VERTEX, Bag::CONJUNCTION_AND],
            ]);
            $bag->limit = 3;
            $bag->groupBy = ['field1'];

            $expected = $this->getExpectedCommand('select-group-by');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command');
        });
    }

    public function testInsert()
    {
        $this->specify("it inserts (C) a single vertex", function () {
            $bag = new Bag();
            $bag->create = [[
                Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                Bag::ELEMENT_LABEL => 'person',
                'name' => 'michael'
            ]];

            $expected = $this->getExpectedCommand('insert-simple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it inserts (C) multiple vertices", function () {
            $bag = new Bag();
            $bag->create = [
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'person',
                    'name' => 'michael'
                ],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    Bag::ELEMENT_LABEL => 'target',
                    'name' => 'dylan'
                ],
                [
                    Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                    'name' => 'peter'
                ],
            ];

            $expected = $this->getExpectedCommand('insert-multiple');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for multiple insert');
        });
    }

    /* Scenario Tests */
    public function testCreateEdges()
    {
        $this->specify("finds (R) two vertices and creates (C) an edge in between", function () {
            $bag = new Bag([
                'create' => [
                    [
                        Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                        Bag::ELEMENT_LABEL => 'knows',
                        Bag::EDGE_INV => new Bag([
                            'retrieve' => [],
                            'where' => [
                                ['name', Bag::COMPARATOR_EQUAL, "peter", Bag::CONJUNCTION_AND],
                            ],
                        ]),
                        Bag::EDGE_OUTV => new Bag([
                            'retrieve' => [],
                            'where' => [
                                ['name', Bag::COMPARATOR_EQUAL, "josh", Bag::CONJUNCTION_AND],
                            ],
                        ]),
                    ],
                ]
            ]);

            $expected = $this->getExpectedCommand('find-two-vertices-and-create-edge');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });

        $this->specify("it creates (C) two vertices and creates (C) an edge in between (R)", function () {
            $bag = new Bag([
                'create' => [
                    [
                        Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                        Bag::ELEMENT_LABEL => 'person',
                        'name' => 'michael'
                    ],
                    [
                        Bag::ELEMENT_TYPE => Bag::ELEMENT_VERTEX,
                        Bag::ELEMENT_LABEL => 'person',
                        'name' => 'dylan'
                    ],
                    [
                        Bag::ELEMENT_TYPE => Bag::ELEMENT_EDGE,
                        Bag::ELEMENT_LABEL => 'knows',
                        Bag::EDGE_INV => new Bag([
                            'retrieve' => [],
                            'where' => [
                                ['name', Bag::COMPARATOR_EQUAL, "michael", Bag::CONJUNCTION_AND],
                            ],
                        ]),
                        Bag::EDGE_OUTV => new Bag([
                            'retrieve' => [],
                            'where' => [
                                ['name', Bag::COMPARATOR_EQUAL, "dylan", Bag::CONJUNCTION_AND],
                            ],
                        ]),
                    ],
                ]
            ]);

            $expected = $this->getExpectedCommand('create-two-vertices-and-create-edge');

            $actual = $this->processor()->process($bag);
            $this->assertEquals($expected, $actual, 'failed to return expected Command for simple select bag');
        });
    }

    /* Internals */
    public function getExpectedCommand($alias)
    {
        $method = $this->camelCase($alias);
        return $this->$method();
    }

    protected function camelCase($alias)
    {
        $key = str_replace("-", " ", $alias);
        $key = ucwords($key);
        $key = str_replace(" ", "", $key);
        $key = lcfirst($key);
        return $key;
    }

    protected function getData()
    {
        return ['one' => 1, 'two' => 'two', 'three' => false];
    }

    protected function getWheres()
    {
        return [
            ['one', Bag::COMPARATOR_EQUAL, 'one', Bag::CONJUNCTION_AND],
            ['two', Bag::COMPARATOR_GT, 2, Bag::CONJUNCTION_AND],
            ['three', Bag::COMPARATOR_LT, 3.14, Bag::CONJUNCTION_OR],
            ['four', Bag::COMPARATOR_EQUAL, true, Bag::CONJUNCTION_AND]
        ];
    }

    /* Methods to Implement */
    /** Returns a valid CommandProcessor */
    abstract public function processor();

    /**
     * Returns a command for the the Bag tested in
     * testInsert:it processes a simple insert bag
     */
    abstract public function insertSimple();

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a simple update bag
     */
//    abstract public function updateSimple();

    /**
     * Returns a command for the the Bag tested in
     * testUpdate:it processes a complex update bag
     */
//    abstract public function updateComplex();

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a simple delete bag
     */
//    abstract public function deleteSimple();

    /**
     * Returns a command for the the Bag tested in
     * testDelete:it processes a complex delete bag
     */
//    abstract public function deleteComplex();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a simple select bag
     */
    abstract public function selectSimple();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a select bag with here constraints
     */
    abstract  public function selectConstraints();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex group by select bag
     */
    abstract public function selectGroupBy();

    /**
     * Returns a command for the the Bag tested in
     * testSelect:it processes a complex order byselect bag
     */
    abstract public function selectOrderBy();
}
