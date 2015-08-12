<?php
namespace Spider\Test\Unit\Drivers\Neo4J;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Drivers\Neo4J\Driver as Neo4JDriver;
use Spider\Test\Unit\Drivers\BaseTestSuite;

/**
 * Tests the Neo4j driver against the standard Driver Test Suite
 * Must implement all methods. See Drivers\BaseTestSuite for more information
 */
class DriverTest extends BaseTestSuite
{
    public function setup()
    {
//        $this->markTestSkipped("Test Database Not Installed");
    }

    /** Returns an instance of the configured driver
     * @param null $switch
     * @return \Spider\Drivers\DriverInterface|Neo4JDriver
     */
    public function driver($switch = null)
    {
        return new Neo4JDriver([
            'hostname' => 'localhost',
            'port' => 7474,
            'username' => "neo4j",
            'password' => "j4oen",
        ]);
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
        return [
            'command' => new Command(
                "MATCH (a {name:'marko'})
                 RETURN a
                 LIMIT 1"
            ),
            'expected' => [
                [
                    'id' => 0,
                    'label' => "person",
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
            'command' => new Command(
                "MATCH (a)
                 RETURN a
                 LIMIT 2"
            ),
            'expected' => [
                [
                    'id' => 0,
                    'label' => "person",
                    'name' => "marko",
                ],
                [
                    'id' => 1,
                    'label' => "person",
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
                "MATCH (a {name:'$name'}) RETURN a"
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
        return [
            'command' => new Command("CREATE (a {name:'testVertex'}) RETURN a"),
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
        $query = "MATCH (a {name:'$name'})
                    SET a.name = 'testVertex2'
                    RETURN a";

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
        $query = "MATCH (a {name:'$name'})
                    DELETE a";

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
        switch($type) {
            case 'int':
                return [[10]];

            case 'string':
                return [['string']];

            case 'boolean':
                return [[true]];
        }
        return [10];
    }

    /* Neo4j Tests */
    public function testFormatPath()
    {
        $driver = $this->driver();
        $driver->open();
        $response = $driver->executeReadCommand(new Command(
            "MATCH p =((a)-[:created]->(b)<-[:created]-(c))
             RETURN p
             LIMIT 1"
        ));
        $consistent = $response->getPath();
        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');
        //First path
        $this->assertTrue(is_array($consistent[0]), 'the formatted response first path is not an array');
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][0], 'Path formating did not properly work for single entry');
        $this->assertEquals(3, $consistent[0][0]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[0][0]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('josh', $consistent[0][0]->name, "name wasn't properly populated");
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][1], 'Path formating did not properly work for single entry');
        $this->assertEquals(4, $consistent[0][1]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('software', $consistent[0][1]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('lop', $consistent[0][1]->name, "name wasn't properly populated");
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][2], 'Path formating did not properly work for single entry');
        $this->assertEquals(2, $consistent[0][2]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[0][2]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('peter', $consistent[0][2]->name, "name wasn't properly populated");
        $response = $driver->executeReadCommand(new Command(
            "MATCH p =((a)-[:created]->(b)<-[:created]-(c))
             RETURN p
             LIMIT 2"
        ));
        $consistent = $response->getPath();
        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');
        //First path
        $this->assertTrue(is_array($consistent[0]), 'the formatted response first path is not an array');
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][0], 'Path formating did not properly work for single entry');
        $this->assertEquals(3, $consistent[0][0]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[0][0]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('josh', $consistent[0][0]->name, "name wasn't properly populated");
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][1], 'Path formating did not properly work for single entry');
        $this->assertEquals(4, $consistent[0][1]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('software', $consistent[0][1]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('lop', $consistent[0][1]->name, "name wasn't properly populated");
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][2], 'Path formating did not properly work for single entry');
        $this->assertEquals(2, $consistent[0][2]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[0][2]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('peter', $consistent[0][2]->name, "name wasn't properly populated");
        //Second path
        $this->assertTrue(is_array($consistent[1]), 'the formatted response first path is not an array');
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][0], 'Path formating did not properly work for single entry');
        $this->assertEquals(3, $consistent[1][0]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[1][0]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('josh', $consistent[1][0]->name, "name wasn't properly populated");
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][1], 'Path formating did not properly work for single entry');
        $this->assertEquals(4, $consistent[1][1]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('software', $consistent[1][1]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('lop', $consistent[1][1]->name, "name wasn't properly populated");
        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][2], 'Path formating did not properly work for single entry');
        $this->assertEquals(0, $consistent[1][2]->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[1][2]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('marko', $consistent[1][2]->name, "name wasn't properly populated");
    }

    public function testFormatTree()
    {
        $this->markTestSkipped("Tree is not yet implemented as gremlin-server doesn't curently support it");
    }
}
