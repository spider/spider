<?php
namespace Spider\Test\Unit\Drivers\Gremlin;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Drivers\Gremlin\Driver as GremlinDriver;
use Spider\Test\Unit\Drivers\BaseTestSuite;

/**
 * Tests the Neo4j driver against the standard Driver Test Suite
 * Must implement all methods. See Drivers\BaseTestSuite for more information
 */
class DriverTest extends BaseTestSuite
{
    public $traversal;
    public $graph;

    public function setup()
    {
        $this->markTestSkipped("Test Database Not Installed");
    }

    /* Implemented Methods */
    /** Returns an instance of the configured driver */
    public function driver($switch = null)
    {
        if ($switch == 'transaction') {
            return new GremlinDriver([
                'hostname' => 'localhost',
                'port' => 8182,
                'graph' => 'graphT',
                'traversal' => 't'
            ]);

        } else {
            return new GremlinDriver([
                'hostname' => 'localhost',
                'port' => 8182,
                'graph' => 'graph',
                'traversal' => 'g'
            ]);
        }

    }

    public function testTransactions()
    {
        return new GremlinDriver([
            'hostname' => 'localhost',
            'port' => 8182,
            'graph' => 'graph',
            'traversal' => 'g'
        ]);
        parent::testTransactions();
    }

    /**
     * Command selects exactly one record
     * Expected: a single array with: id, name, label
     * @return array [
     *  [
     *      'command' => new Command("SPECIFIC SCRIPT HERE"),
     *      'expected' => [
     *          [
     *              'id' => 'RETURNED ID',
     *              'name' => 'RESULT.NAME',
     *              'label' => 'RESULT.LABEL'
     *          ]
     *      ]
     *  ]
     */
    public function selectOneItem()
    {
        $query = $this->driver('transaction')->traversal . ".V().has('name', 'marko').limit(1)";
        return [
            'command' => new Command($query),
            'expected' => [
                [
                    'id' => 1,
                    'label' => "vertex",
                    'name' => "marko",
                ]
            ]
        ];
    }

    /**
     * Command selects exactly two records
     * Expected: two arrays, each with: id, name, label
     * @return array [
     *  [
     *      'command' => new Command("SPECIFIC SCRIPT HERE"),
     *      'expected' => [
     *          [
     *              'id' => 'FIRST RETURNED ID',
     *              'name' => 'FIRST RESULT.NAME',
     *              'label' => 'FIRST RESULT.LABEL'
     *          ],
     *          [
     *              'id' => 'SECOND RESULT.ID',
     *              'name' => 'SECOND RESULT.NAME',
     *              'label' => 'SECOND RESULT.LABEL'
     *          ],
     *      ]
     *  ]
     */
    public function selectTwoItems()
    {
        return [
            'command' => new Command($this->driver('transaction')->traversal . ".V().limit(2)"),
            'expected' => [
                [
                    'id' => 1,
                    'label' => "vertex",
                    'name' => "marko",
                ],
                [
                    'id' => 2,
                    'label' => "vertex",
                    'name' => 'vadas'
                ]
            ]
        ];
    }

    /**
     * Command selects exactly one record by name = $name
     * Expected: Not used. Return an empty array
     * @param $name
     * @return array
     */
    public function selectByName($name)
    {
        return [
            'command' => new Command(
                $this->driver('transaction')->traversal . ".V().has('name', '$name')"
            ),
            'expected' => []
        ];
    }

    /**
     * Command creates a single record with a name
     * Expected: a single array with: `name` created
     * @return array
     */
    public function createOneItem()
    {
        $query = $this->driver('transaction')->graph . ".addVertex('name', 'testVertex')";

        return [
            'command' => new Command($query),
            'expected' => [
                [
                    'name' => 'testVertex',
                ]
            ]
        ];
    }

    /**
     * Command updates a single item by name = ?, changing the name
     * Expected: a single array with: name
     * @param $name
     * @return array
     */
    public function updateOneItem($name)
    {
        $query = $this->driver('transaction')->traversal . ".V().has('name', '$name').property('name', 'testVertex2')";

        return [
            'command' => new Command($query),
            'expected' => [
                [
                    'name' => 'testVertex2'
                ]
            ]
        ];
    }

    /**
     * Command deletes a single item by name = ?
     * Expected: an empty array
     * @param $name
     * @return array
     */
    public function deleteOneItem($name)
    {
        $query = $this->driver('transaction')->traversal . ".V().has('name', '$name').drop().iterate()";

        return [
            'command' => new Command($query),
            'expected' => [],
        ];
    }

    /**
     * Returns the name of a meta property used by the driver
     * @return string
     */
    public function getMetaKey()
    {
        return 'id';
    }

    /**
     * Returns the response needed to formatAsScalar()
     * Must switch between int, string, boolean
     * @param $type
     * @return array
     */
    public function getScalarResponse($type)
    {
        switch ($type) {
            case 'int':
                return [10];

            case 'string':
                return ['string'];

            case 'boolean':
                return [true];
        }
        return [10];
    }

    /* Gremlin Tests */
    public function testFormatPath()
    {
        $driver = $this->driver();

        // test single result
        $response = [
            [
                'labels' => [[], []],
                'objects' => [
                    [
                        'id' => 430,
                        'label' => 'user',
                        'type' => 'vertex',
                        'properties' => [
                            'name' => [
                                [
                                    'id' => 431,
                                    'value' => 'dylan',
                                ]
                            ]
                        ],
                    ],
                    [
                        'id' => 480,
                        'label' => 'user',
                        'type' => 'vertex',
                        'properties' => [
                            'name' => [
                                [
                                    'id' => 432,
                                    'value' => 'chris',
                                ]
                            ]
                        ],
                    ]
                ]
            ],
            [
                'labels' => [[], []],
                'objects' => [
                    [
                        'id' => 480,
                        'label' => 'user',
                        'type' => 'vertex',
                        'properties' => [
                            'name' => [
                                [
                                    'id' => 432,
                                    'value' => 'chris',
                                ]
                            ]
                        ],
                    ],
                    [
                        'id' => 430,
                        'label' => 'user',
                        'type' => 'vertex',
                        'properties' => [
                            'name' => [
                                [
                                    'id' => 431,
                                    'value' => 'dylan',
                                ]
                            ]
                        ],
                    ],
                ]
            ]
        ];
        $consistent = $driver->formatAsPath($response);
        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');

        //First path
        $this->assertTrue(is_array($consistent[0]), 'the formatted response first path is not an array');

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][0], 'Path formating did not properly work for single entry');
        $this->assertEquals(430, $consistent[0][0]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('user', $consistent[0][0]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('vertex', $consistent[0][0]->meta()->type, "type wasn't properly populated");
        $this->assertEquals('dylan', $consistent[0][0]->name, "name wasn't properly populated");

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][1], 'Path formating did not properly work for single entry');
        $this->assertEquals(480, $consistent[0][1]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('user', $consistent[0][1]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('vertex', $consistent[0][1]->meta()->type, "type wasn't properly populated");
        $this->assertEquals('chris', $consistent[0][1]->name, "name wasn't properly populated");

        //Second Path
        $this->assertTrue(is_array($consistent[1]), 'the formatted response second path is not an array');

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1][0], 'Path formating did not properly work for single entry');
        $this->assertEquals(480, $consistent[1][0]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('user', $consistent[1][0]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('vertex', $consistent[1][0]->meta()->type, "type wasn't properly populated");
        $this->assertEquals('chris', $consistent[1][0]->name, "name wasn't properly populated");

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1][1], 'Path formating did not properly work for single entry');
        $this->assertEquals(430, $consistent[1][1]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('user', $consistent[1][1]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('vertex', $consistent[1][1]->meta()->type, "type wasn't properly populated");
        $this->assertEquals('dylan', $consistent[1][1]->name, "name wasn't properly populated");
    }

    public function testFormatTree()
    {
        $this->markTestSkipped("Tree is not yet implemented as gremlin-server doesn't currently support it");
    }
}
