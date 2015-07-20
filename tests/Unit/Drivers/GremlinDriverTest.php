<?php
namespace Michaels\Spider\Test\Unit\Drivers;

use Codeception\Specify;

use Michaels\Spider\Drivers\Gremlin\Driver as GremlinDriver;
use Michaels\Spider\Queries\Command;

class GremlinDriverTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $config;
    protected $credentials;

    public function setup()
    {
        $this->markTestSkipped('The Test Database is not installed');

        $this->credentials = [
            'hostname' => 'localhost',
            'port' => 8182,
            'graph' => 'graph',
            'traversal'=> 'g'
        ];
    }

    public function testConnections()
    {
        $this->specify("it opens and closes the database without exception", function () {
            $driver = new GremlinDriver();
            $driver->open($this->credentials);
            $driver->close();
        });
    }

    public function testReadCommands()
    {
        $this->specify("it selects a single record and returns an array of Records", function () {
            $driver = new GremlinDriver();
            $driver->open($this->credentials);

            $response = $driver->executeReadCommand(new Command(
                $this->credentials['traversal'].".V().has('name', 'marko').limit(1)"
            ));

            $driver->close();

            $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $response, 'failed to return a Record');
            $this->assertEquals("marko", $response->properties['name'], "failed to return the correct names");
        });

        $this->specify("it selects multiple unrelated records and returns an array of Records", function () {
            $driver = new GremlinDriver();
            $driver->open($this->credentials);

            $response = $driver->executeReadCommand(new Command(
                $this->credentials['traversal'].".V()"
            ));

            $driver->close();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(6, $response, "failed to return 6 results");
            $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $response[0], 'failed to return records');
        });
    }

    public function testWriteCommands()
    {
        $driver = new GremlinDriver();
        $driver->open($this->credentials);

        // Create new
        $query = $this->credentials['graph'].".addVertex('name', 'testVertex')";
        $newRecord = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $newRecord, 'failed to return a Record');
        $this->assertEquals("testVertex", $newRecord->properties['name'], "failed to return the correct names");

        // Update existing
        $query = $this->credentials['traversal'].".V().has('name', 'testVertex').property('name', 'testVertex2')";
        $updatedRecord = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $updatedRecord, 'failed to return a Record');
        $this->assertEquals("testVertex2", $updatedRecord->properties['name'], "failed to return the correct names");


        // Delete That one
        $query = $this->credentials['traversal'].".V().has('name', 'testVertex2').drop().iterate()";
        $updatedRecord = $driver->executeWriteCommand(new Command($query));

        $this->assertEquals([], $updatedRecord, "failed to delete");

        // And try to get it again
        $response = $driver->executeReadCommand(new Command($this->credentials['traversal'].".V().has('name', 'testVertex2')"));

        $this->assertTrue(is_array($response), 'failed to return an array');
        $this->assertEmpty($response, "failed to return an EMPTY array");

        // Done
        $driver->close();
    }
}