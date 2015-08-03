<?php
namespace Spider\Test\Unit\Drivers\Neo4J;

use Codeception\Specify;

use Spider\Drivers\Neo4J\Driver as Neo4JDriver;
use Spider\Commands\Command;

class DriverTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $config;
    protected $credentials;

    public function setup()
    {
        $this->markTestSkipped('The Test Database is not installed');

        $this->credentials = [
            'hostname' => 'localhost',
            'port' => 7474,
            'username'=> "neo4j",
            'password'=> "j4oen",
        ];
    }

    public function testConnections()
    {
        $this->specify("it opens and closes the database without exception", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $driver->client->getServerInfo(); // will throw an error if connection fails.
            $driver->close();
        });
    }

    public function testReadCommands()
    {
        $this->specify("it selects a single record and returns an array of Records", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();

            $response = $driver->executeReadCommand(new Command(
                    "MATCH (a {name:'marko'})
                     RETURN a
                     LIMIT 1"
            ));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return a Record');
            $this->assertEquals("marko", $response->name, "failed to return the correct names");
            $this->assertEquals("person", $response->label, "failed to return the correct label");
            $this->assertEquals(0, $response->id, "failed to return the correct id");
        });

        $this->specify("it selects multiple unrelated records and returns an array of Records", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();

            $response = $driver->executeReadCommand(new Command(
                "MATCH (a)
                 RETURN a"
            ));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(6, $response, "failed to return 6 results");
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return Response Object');
        });
    }

     public function testWriteCommands()
    {
        $driver = new Neo4JDriver($this->credentials);
        $driver->open();

        // Create new
        $query = "CREATE (a {name:'testVertex'}) RETURN a";
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $newRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $newRecord, 'failed to return a Record');
        $this->assertEquals("testVertex", $newRecord->name, "failed to return the correct names");

        // Update existing
        $query = "MATCH (a {name:'testVertex'})
                    SET a.name = 'testVertex2'
                    RETURN a";
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $updatedRecord, 'failed to return a Record');
        $this->assertEquals("testVertex2", $updatedRecord->name, "failed to return the correct names");


        // Delete That one
        $query = "MATCH (a {name:'testVertex2'})
                    DELETE a";
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertEquals([], $updatedRecord, "failed to delete");

        // And try to get it again
        $response = $driver->executeReadCommand(new Command("MATCH (a {name:'testVertex2'}) RETURN a"));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $response = $response->getSet();

        $this->assertTrue(is_array($response), 'failed to return an array');
        $this->assertEmpty($response, "failed to return an EMPTY array");

        // Done
        $driver->close();
    }

     public function testTransactions()
    {
        //get a transaction enabled graph

        $this->specify("it rollbacks properly on transactional graph", function () {

            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $driver->StartTransaction();

            $response = $driver->executeWriteCommand(new Command(
                "CREATE (a {name:'testVertex'}) RETURN a"
            ));

            $driver->StopTransaction(FALSE);

            $response = $driver->executeReadCommand(new Command("MATCH (n) RETURN count(n)"));
            $count = $response->getScalar();

            $this->assertEquals(6, $count, "the rollback did not properly work");
            $driver->close();
        });

        $this->specify("it commits properly on transactional graph", function () {

            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $driver->StartTransaction();

            $response = $driver->executeWriteCommand(new Command(
                "CREATE (a {name:'testVertex'}) RETURN a"
            ));

            $driver->StopTransaction();

            $response = $driver->executeReadCommand(new Command("MATCH (n) RETURN count(n)"));
            $count = $response->getScalar();

            $this->assertEquals(7, $count, "the rollback did not properly work");

            // Delete That one
            $query = "MATCH (z {name:'testVertex'}) DELETE z";
            $driver->runWriteCommand(new Command($query));

            $driver->close();
        });

        $this->specify("it throws an Exception on multiple transaction", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $driver->StartTransaction();
            $driver->StartTransaction();
            $driver->close();
        }, ['throws'=> new \Spider\Exceptions\InvalidCommandException]);

        $this->specify("it throws an Exception when a non existing transaction is stopped", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $driver->StopTransaction();
            $driver->close();
        }, ['throws'=> new \Spider\Exceptions\InvalidCommandException]);
    }

    public function testFormatScalar()
    {
        $driver = new Neo4JDriver();

        $response = [[10]];
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals(10, $consistent, 'Scalar formating did not properly work with Int');

        $response = [['string']];
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals('string', $consistent, 'Scalar formating did not properly work with String');

    }

    public function testFormatSet()
    {
        $driver = new Neo4JDriver($this->credentials);
        $driver->open();

        // test single result
        $response = $driver->executeReadCommand(new Command(
                "MATCH (a {name:'marko'})
                 RETURN a
                 LIMIT 1"
        ));

        $consistent = $response->getSet();
        $this->assertInstanceOf('Spider\Base\Collection', $consistent, 'Set formating did not properly work for single entry');
        $this->assertEquals(0, $consistent->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent->meta()->label, "label wasn't properly populated");
        $this->assertEquals('marko', $consistent->name, "name wasn't properly populated");

        // test multiple results
        $response = $driver->executeReadCommand(new Command(
                "MATCH (a:person)
                 RETURN a
                 ORDER BY a.name
                 LIMIT 2"
        ));

        $consistent = $response->getSet();
        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'Set formating did not properly work for single entry');
        $this->assertEquals(3, $consistent[0]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[0]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('josh', $consistent[0]->name, "name wasn't properly populated");

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1], 'Set formating did not properly work for single entry');
        $this->assertEquals(0, $consistent[1]->meta()->id, "id wasn't properly populated");
        $this->assertEquals('person', $consistent[1]->meta()->label, "label wasn't properly populated");
        $this->assertEquals('marko', $consistent[1]->name, "name wasn't properly populated");
    }

    public function testFormatPath()
    {
        $driver = new Neo4JDriver($this->credentials);
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

    /**
     * Check the id and label in Response are protected.
     */
    public function testProtectedResponse()
    {
        $this->specify("it throws an Exception when a modifying protected id", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $response = $driver->executeReadCommand(new Command(
                "MATCH (a {name:'marko'})
                 RETURN a
                 LIMIT 1"
            ));
            $consistent = $response->getSet();
            $this->assertEquals(0, $consistent->id, "incorrect id found");
            $this->assertEquals("person", $consistent->label, "incorrect label found");

            $consistent->id = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected label", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $response = $driver->executeReadCommand(new Command(
                "MATCH (a {name:'marko'})
                 RETURN a
                 LIMIT 1"
            ));
            $consistent = $response->getSet();
            $this->assertEquals(0, $consistent->id, "incorrect id found");
            $this->assertEquals("person", $consistent->label, "incorrect label found");

            $consistent->label = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected meta", function () {
            $driver = new Neo4JDriver($this->credentials);
            $driver->open();
            $response = $driver->executeReadCommand(new Command(
                "MATCH (a {name:'marko'})
                 RETURN a
                 LIMIT 1"
            ));
            $consistent = $response->getSet();
            $this->assertEquals(0, $consistent->id, "incorrect id found");
            $this->assertEquals("person", $consistent->label, "incorrect label found");

            $consistent->meta()->id = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);
    }

}
