<?php
namespace Spider\Test\Unit\Drivers\Neo4J;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Drivers\Neo4J\Driver as Neo4JDriver;
use Spider\Test\Fixtures\Graph;
use Spider\Test\Unit\Drivers\BaseTestSuite;

/**
 * Tests the Neo4j driver against the standard Driver Test Suite
 * Must implement all methods. See Drivers\BaseTestSuite for more information
 */
class DriverTest extends BaseTestSuite
{
    public function setup()
    {
        $this->markTestSkipped("Test Database Not Installed");
    }

    /** Returns an instance of the configured driver
     * @param null $switch
     * @return \Spider\Drivers\DriverInterface|Neo4JDriver
     */
    public function driver($switch = null)
    {
        return new Neo4JDriver(Graph::$servers['neo4j']);
    }

    /**
     * Command selects exactly one record from "person"
     * @return Command
     */
    public function selectOneItem()
    {
        return new Command(
            "MATCH (a {name:'marko'})
             RETURN a
             LIMIT 1"
        );
    }

    /**
     * Command selects exactly the first two records from "person"
     * @return Command
     */
    public function selectTwoItems()
    {
        return new Command(
            "MATCH (a)
             RETURN a
             LIMIT 2"
        );
    }

    /**
     * Command selects exactly one record by name = $name
     * @param $name
     * @return Command
     */
    public function selectByName($name)
    {
        return new Command(
            "MATCH (a {name:'$name'}) RETURN a"
        );
    }

    /**
     * Command creates a single record with the name "testVertex"
     * @return Command
     */
    public function createOneItem()
    {
        return new Command("CREATE (a {name:'testVertex'}) RETURN a");
    }

    /**
     * Command updates a single item by name = ?, changing the name to "testVertex2"
     * @param $name
     * @return Command
     */
    public function updateOneItem($name)
    {
        $query = "MATCH (a {name:'$name'})
                    SET a.name = 'testVertex2'
                    RETURN a";

        return new Command($query);
    }

    /**
     * Command deletes a single item by name = ?
     * @param $name
     * @return Command
     */
    public function deleteOneItem($name)
    {
        $query = "MATCH (a {name:'$name'})
                    DELETE a";

        return new Command($query);
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
