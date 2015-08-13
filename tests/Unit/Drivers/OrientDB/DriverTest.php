<?php
namespace Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Drivers\OrientDB\Driver as OrientDriver;
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
     * @return OrientDriver
     */
    public function driver($switch = null)
    {
        return new OrientDriver([
            'hostname' => 'localhost',
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
            'database' => 'spider_test_graph'
        ]);
    }

    /**
     * Command selects exactly one record from "person"
     * @return Command
     */
    public function selectOneItem()
    {
        return new Command("SELECT FROM person WHERE name = 'marko' LIMIT 1");
    }

    /**
     * Command selects exactly the first two records from "person"
     * @return Command
     */
    public function selectTwoItems()
    {
        return new Command(
            "SELECT FROM person LIMIT 2"
        );
    }

    /**
     * Command selects exactly one record by name = $name
     * @return Command
     */
    public function selectByName($name)
    {
        return new Command("SELECT FROM V WHERE name = '$name'");
    }

    /**
     * Command creates a single record with the name "testVertex"
     * @return Command
     */
    public function createOneItem()
    {
        return new Command(
            "CREATE Vertex V CONTENT " . json_encode(['name' => 'testVertex'])
        );
    }

    /**
     * Command updates a single item by name = ?, changing the name to "testVertex2"
     * @param $name
     * @return Command
     */
    public function updateOneItem($name)
    {
        $query = "UPDATE (SELECT FROM V WHERE name='$name') ";
        $query .= "MERGE " . json_encode(['name' => 'testVertex2']) . ' RETURN AFTER $current';

        return new Command($query);
    }

    /**
     * Command deletes a single item by name = ?
     * @param $name
     * @return Command
     */
    public function deleteOneItem($name)
    {
        return new Command("DELETE VERTEX V WHERE name = '$name'");
    }

    /**
     * Returns the name of a meta property used by the driver
     * @return string
     */
    public function getMetaKey()
    {
        return 'rid';
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
                return [10];

            case 'string':
                return ['string'];

            case 'boolean':
                return [true];
        }
        return [10];
    }

    /**
     * Format the id to a vendor-specific format
     * @param int $id
     * @param int $cluster
     * @return mixed
     */
    public function formatId($id, $cluster = 11)
    {
        return "#$cluster:$id";
    }

    /* Orient Specific Tests */
    public function testBuildTransactionStatement()
    {
        $this->specify("it builds a correct transaction", function () {
            $driver = $this->driver();
            $driver->open();
            $driver->startTransaction();

            $driver->executeWriteCommand(new Command(
                "CREATE VERTEX CONTENT {name:'one'}"
            ));

            $driver->executeWriteCommand(new Command(
                "CREATE VERTEX CONTENT {name:'two'}"
            ));

            $driver->executeWriteCommand(new Command(
                "CREATE VERTEX CONTENT {name:'three'}"
            ));

            $expected = "begin\n";
            $expected .= "LET t1 = CREATE VERTEX CONTENT {name:'one'}\n";
            $expected .= "LET t2 = CREATE VERTEX CONTENT {name:'two'}\n";
            $expected .= "LET t3 = CREATE VERTEX CONTENT {name:'three'}\n";
            $expected .= 'commit return [$t1,$t2,$t3]';

            $driver->stopTransaction(false); // false

            $actual = $driver->getTransactionForTest();

            $this->assertEquals($expected, $actual, "the transaction statement was incorrectly built");
            $driver->close();
        });
    }

    /* Override Not Supported Features */
    public function testFormatTree()
    {
        $this->markTestSkipped("Tree is not yet implemented as orient doesn't currently support it");
    }

    public function testFormatPath()
    {
        $this->markTestSkipped("Path is not yet implemented as orient doesn't currently support it");
    }
}
