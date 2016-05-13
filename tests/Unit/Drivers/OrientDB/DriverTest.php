<?php
namespace Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use Spider\Commands\Builder;
use Spider\Commands\Command;
use Spider\Drivers\OrientDB\Driver as OrientDriver;
use Spider\Test\Fixtures\Graph;
use Spider\Test\Fixtures\OrientFixture;
use Spider\Test\Unit\Drivers\BaseTestSuite;

/**
 * Tests the Neo4j driver against the standard Driver Test Suite
 * Must implement all methods. See Drivers\BaseTestSuite for more information
 */
class DriverTest extends BaseTestSuite
{
    public function setup()
    {
        $this->fixture = new OrientFixture();
        $this->fixture->unload();
        $this->fixture->load();
    }

    public function teardown()
    {
        $this->fixture->unload();
    }

    /** Returns an instance of the configured driver
     * @param null $switch
     * @return OrientDriver
     */
    public function driver($switch = null)
    {
        return new OrientDriver([
            'hostname' => 'orientdb',
            'port' => 2424,
            'username' => 'root',
            'password' => "rootpwd",
            'database' => 'modern_graph',
        ]);
    }

    /**
     * Command selects exactly one record from "person"
     * @return Command
     */
    public function selectOneItem()
    {
        return new Command("SELECT FROM person WHERE name = 'marko' LIMIT 1", 'orientSQL');
    }

    /**
     * Command selects exactly the first two records from "person"
     * @return Command
     */
    public function selectTwoItems()
    {
        return new Command(
            "SELECT FROM person LIMIT 2", 'orientSQL'
        );
    }

    /**
     * Command selects exactly one record by name = $name
     * @return Command
     */
    public function selectByName($name)
    {
        return new Command("SELECT FROM V WHERE name = '$name'", 'orientSQL');
    }

    /**
     * Command creates a single record with the name "testVertex"
     * @return Command
     */
    public function createOneItem()
    {
        return new Command(
            "CREATE Vertex V CONTENT " . json_encode(['name' => 'testVertex']),
            'orientSQL'
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

        return new Command($query, 'orientSQL');
    }

    /**
     * Command deletes a single item by name = ?
     * @param $name
     * @return Command
     */
    public function deleteOneItem($name)
    {
        return new Command("DELETE VERTEX V WHERE name = '$name'", 'orientSQL');
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

    /* Orient specific tests */
    public function testBuildTransactionStatement()
    {
        $this->specify("it builds a correct transaction", function () {
            $driver = $this->driver();
            $driver->open();
            $driver->startTransaction();

            $driver->executeCommand(new Command(
                "CREATE VERTEX CONTENT {name:'one'}", "orientSQL"
            ));

            $driver->executeCommand(new Command(
                "CREATE VERTEX CONTENT {name:'two'}", "orientSQL"
            ));

            $driver->executeCommand(new Command(
                "CREATE VERTEX CONTENT {name:'three'}", "orientSQL"
            ));

            $expected = "begin\n";
            $expected .= "LET t1 = CREATE VERTEX CONTENT {name:'one'}\n";
            $expected .= "LET t2 = CREATE VERTEX CONTENT {name:'two'}\n";
            $expected .= "LET t3 = CREATE VERTEX CONTENT {name:'three'}\n";
            $expected .= "commit retry 100\n";
            $expected .= 'return [$t1,$t2,$t3]';

            $actual = $driver->getTransactionForTest();

            $driver->stopTransaction(false); // false

            $this->assertEquals($expected, $actual, "the transaction statement was incorrectly built");
            $driver->close();
        });
    }

    public function testClassNotNotExist()
    {
        $this->specify("it throws an exception if inserting into non-existant class", function () {
            $driver = $this->driver();
            $driver->open();

            $driver->executeCommand(new Command(
                "INSERT INTO nothing CONTENT {name: 'michael'}",
                "orientSQL"
            ));

            $driver->close();
        }, ['throws' => 'Spider\Drivers\OrientDB\ClassDoesNotExistException']);
    }

    public function testFormatSingleCollectionAsScalar()
    {
        $this->specify("it formats a single response as a scalar", function () {
            $driver = $this->driver();
            $driver->open();

            $response = $driver->executeCommand(new Command(
                "SELECT name FROM V LIMIT 1", 'orientSQL',
                "orientSQL"
            ));

            $scalar = $response->getScalar();

            $driver->close();

            $this->assertTrue(is_string($scalar), "failed to return a string");
            $this->assertEquals($this->expected[0]['name'], $scalar, "failed to return the correct scalar");
        });

        $this->specify("it throws exception for multiple projections", function () {
            $driver = $this->driver();
            $driver->open();

            $response = $driver->executeCommand(new Command(
                "SELECT FROM V LIMIT 1", 'orientSQL',
                "orientSQL"
            ));

            $driver->close();
            $response->getScalar();
        }, ['throws' => 'Spider\Exceptions\FormattingException']);

        $this->specify("it throws exception for multiple records", function () {
            $driver = $this->driver();
            $driver->open();

            $response = $driver->executeCommand(new Command(
                "SELECT name FROM V", 'orientSQL',
                "orientSQL"
            ));

            $driver->close();
            $response->getScalar();
        }, ['throws' => 'Spider\Exceptions\FormattingException']);
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

    public function testPassingBuilder()
    {
        $builder = new Builder();
        $builder->select()->from('V');
        $driver = $this->driver();
        $driver->open();

        $response = $driver->executeCommand($builder);

        $consistent = $response->getSet();
        $this->assertEquals(6, count($consistent), "wrong number of elements found");
    }
}
