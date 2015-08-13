<?php
namespace Spider\Test\Unit\Drivers\Gremlin;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Drivers\Gremlin\Driver as GremlinDriver;
use Spider\Test\Fixtures\Graph;
use Spider\Test\Unit\Drivers\BaseTestSuite;

/**
 * Tests the Neo4j driver against the standard Driver Test Suite
 * Must implement all methods. See Drivers\BaseTestSuite for more information
 */
class DriverTest extends BaseTestSuite
{
    protected $driver;

    public function setup()
    {
        //$this->markTestSkipped("Test Database Not Installed");
    }

    /* Implemented Methods */
    /** Returns an instance of the configured driver */
    public function driver($switch = null)
    {
        if ($switch == 'transaction') {
            return $this->driver = new GremlinDriver(Graph::$servers['gremlin-transaction']);

        } else {
            return $this->driver = new GremlinDriver(Graph::$servers['gremlin']);
        }

    }

    /**
     * Command selects exactly one record from "person"
     * @return Command
     */
    public function selectOneItem()
    {
        $query = $this->driver->traversal . ".V().has('name', 'marko').limit(1)";
        return new Command($query, 'gremlin');
    }

    /**
     * Command selects exactly the first two records from "person"
     * @return Command
     */
    public function selectTwoItems()
    {
        return new Command($this->driver->traversal . ".V().limit(2)", 'gremlin');
    }

    /**
     * Command selects exactly one record by name = $name
     * @param $name
     * @return Command
     */
    public function selectByName($name)
    {
        return new Command(
            $this->driver->traversal . ".V().has('name', '$name', 'gremlin')"
        );
    }

    /**
     * Command creates a single record with the name "testVertex"
     * @return Command
     */
    public function createOneItem()
    {
        $query = $this->driver->graph . ".addVertex('name', 'testVertex')";
        return new Command($query, 'gremlin');
    }

    /**
     * Command updates a single item by name = ?, changing the name to "testVertex2"
     * @param $name
     * @return Command
     */
    public function updateOneItem($name)
    {
        $query = $this->driver->traversal . ".V().has('name', '$name').property('name', 'testVertex2')";
        return new Command($query, 'gremlin');
    }

    /**
     * Command deletes a single item by name = ?
     * @param $name
     * @return Command
     */
    public function deleteOneItem($name)
    {
        $query = $this->driver->traversal . ".V().has('name', '$name').drop().iterate()";

        return new Command($query, 'gremlin');
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
