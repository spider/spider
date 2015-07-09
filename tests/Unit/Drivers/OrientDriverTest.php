<?php
namespace Michaels\Spider\Test\Unit\Drivers;

use Codeception\Specify;
use Michaels\Spider\Drivers\OrientDB\Driver as OrientDriver;
use Michaels\Spider\Queries\Command;

class OrientDriverTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $config;
    protected $credentials;

    public function setup()
    {
//        $this->markTestSkipped('The Test Database is not installed');

        $this->credentials = [
            'hostname' => 'localhost',
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
            'database' => 'spider-test'
        ];
    }

    public function testConnections()
    {
        $this->specify("it opens and closes the database without exception", function () {
            $driver = new OrientDriver();
            $driver->open($this->credentials);
            $driver->close();
        });
    }

    public function testReadCommands()
    {
        $this->specify("it selects a single record and returns an array of Records", function () {
            $driver = new OrientDriver();
            $driver->open($this->credentials);

            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM Cat WHERE @rid = #12:0"
            ));

            $driver->close();

            $this->assertTrue(is_array($response), 'failed to return an array');
            $this->assertCount(1, $response, "failed to return 1 result");
            $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $response[0], 'failed to return a Record');
            $this->assertEquals("oreo", $response[0]->name, "failed to return the correct names");
        });

        $this->specify("it selects multiple unrelated records and returns an array of Records", function () {
            $driver = new OrientDriver();
            $driver->open($this->credentials);

            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM Cat"
            ));

            $driver->close();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(2, $response, "failed to return 2 results");
            $this->assertEquals("oreo", $response[0]->name, "failed to return the correct names");
            $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $response[0], 'failed to return records');
        });
    }

    public function testWriteCommands()
    {
        $driver = new OrientDriver();
        $driver->open($this->credentials);

        // Create new
        $sql = "INSERT INTO Owner CONTENT " . json_encode(['first_name' => 'nicole', 'last_name' => 'lowman']);
        $newRecord = $driver->executeWriteCommand(new Command($sql));

        $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $newRecord, 'failed to return a Record');
        $this->assertEquals("nicole", $newRecord->first_name, "failed to return the correct names");


        // Update existing
        $sql = "UPDATE (SELECT FROM Owner WHERE @rid=$newRecord->id) MERGE " . json_encode(['last_name' => 'wilson']) . ' RETURN AFTER $current';
        $updatedRecord = $driver->executeWriteCommand(new Command($sql));

        $this->assertInstanceOf('Michaels\Spider\Graphs\Record', $updatedRecord, 'failed to return a Record');
        $this->assertEquals("wilson", $updatedRecord->last_name, "failed to return the correct names");


        // Delete That one
        $sql = "DELETE VERTEX Owner WHERE @rid=$newRecord->id";
        $updatedRecord = $driver->executeWriteCommand(new Command($sql));

        $this->assertEquals("1", $updatedRecord, "failed to delete exactly one record");

        // And try to get it again
        $response = $driver->executeReadCommand(new Command("SELECT FROM Owner WHERE @rid=$newRecord->id"));

        $this->assertTrue(is_array($response), 'failed to return an array');
        $this->assertEmpty($response, "failed to return an EMPTY array");

        // Done
        $driver->close();
    }
}
