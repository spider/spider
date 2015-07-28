<?php
namespace Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;
use Spider\Commands\Command;
use Spider\Drivers\OrientDB\Driver as OrientDriver;
use Spider\Exceptions\FormattingException;

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
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
            'database' => 'GratefulDeadConcerts'
        ];
    }

    public function testConnections()
    {
        $this->specify("it opens and closes the database without exception", function () {
            $driver = new OrientDriver($this->credentials);
            $driver->open();
            $driver->close();
        });
    }

    public function testReadCommands()
    {
        $this->specify("it selects a single record and returns an array of Records", function () {
            $driver = new OrientDriver($this->credentials);
            $driver->open();

            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM V WHERE @rid = #9:1"
            ));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return a Record');
            $this->assertEquals("HEY BO DIDDLEY", $response->name, "failed to return the correct names");
            $this->assertEquals("V", $response->label, "failed to return the correct label");
            $this->assertEquals('#9:1', $response->id, "failed to return the correct id");
        });

        $this->specify("it selects multiple unrelated records and returns an array of Records", function () {
            $driver = new OrientDriver($this->credentials);
            $driver->open();

            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM V WHERE song_type = 'cover' LIMIT 3"
            ));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(3, $response, "failed to return 3 results");
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return Response Object');
        });
    }

    public function testWriteCommands()
    {
        $driver = new OrientDriver($this->credentials);
        $driver->open();

        // Create new
        $query = "CREATE Vertex CONTENT " . json_encode(['song_type' => 'cover', 'name' => 'New Song']);
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $newRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $newRecord, 'failed to return a Record');
        $this->assertEquals("New Song", $newRecord->name, "failed to return the correct names");

        // Update existing
        $query = "UPDATE (SELECT FROM V WHERE @rid=$newRecord->id) ";
        $query .= "MERGE " . json_encode(['name' => 'Updated Song']) . ' RETURN AFTER $current';

        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $updatedRecord, 'failed to return a Record');
        $this->assertEquals("Updated Song", $updatedRecord->name, "failed to return the correct names");

        // Delete That one
        $query = "DELETE VERTEX $newRecord->id";
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertEquals([], $updatedRecord, "failed to delete");

        // And try to get it again
        $response = $driver->executeReadCommand(new Command("SELECT FROM V WHERE @rid=$newRecord->id"));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $response = $response->getSet();

        $this->assertTrue(is_array($response), 'failed to return an array');
        $this->assertEmpty($response, "failed to return an EMPTY array");

        // Done
        $driver->close();
    }

    public function testFormatScalar()
    {
        $driver = new OrientDriver();

        // Record with int
//        $record= new Record();
//        $record->setOData(['item' => 10]);
//        $response = [$record];
//
//        $consistent = $driver->formatAsScalar($response);
//        $this->assertEquals(10, $consistent, 'Scalar formatting did not properly work with Record Int');
//
//         Record with string
//        $record = new Record();
//        $record->setOData(['item' => 'string']);
//        $response = [$record];
//
//        $consistent = $driver->formatAsScalar($response);
//        $this->assertEquals('string', $consistent, 'Scalar formatting did not properly work with Record String');

        // solo int, string, bool
        $response = [10];
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals(10, $consistent, 'Scalar formatting did not properly work with Int');

        $response = ['string'];
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals('string', $consistent, 'Scalar formatting did not properly work with String');

        $response = [true];
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals(true, $consistent, 'Scalar formatting did not properly work with Bool');
    }

    public function testThrowsFormattingExceptionForScalar()
    {
//        $this->specify("it throws an exception for record with more than one item", function () {
//            $driver = new OrientDriver();
//
//            $record = new Record();
//            $record->setOData(['item' => 10, 'two' => 2]);
//            $response = [$record];
//
//            $driver->formatAsScalar($response);
//        }, ['throws' => new FormattingException()]);
//
//        $this->specify("it throws an exception for multiple records", function () {
//            $driver = new OrientDriver();
//
//            $record = new Record();
//            $another = new Record();
//            $response = [$record, $another];
//
//            $driver->formatAsScalar($response);
//        }, ['throws' => new FormattingException()]);

        $this->specify("it throws an exception for multiple scalar values", function () {
            $driver = new OrientDriver();

            $response = [1,2];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);

        $this->specify("it throws an exception for a non-array", function () {
            $driver = new OrientDriver();

            $response = 3;

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);

        $this->specify("it throws an exception for an array of invalid objects", function () {
            $driver = new OrientDriver();

            $response = [[1]];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);
    }

    public function testFormatSet()
    {
        $driver = new OrientDriver();

        // test single result
        $record = new Record();
        $record->setRid(new ID(1, 1));
        $record->setOClass('user');
        $record->setVersion(1);
        $record->setOData([
            'name' => 'dylan',
        ]);

        $response = [$record];

        $consistent = $driver->formatAsSet($response);
        $this->assertInstanceOf('Spider\Base\Collection', $consistent, 'Set formatting did not work for single entry');
        $this->assertEquals('#1:1', $consistent->id, "id wasn't properly populated");
        $this->assertEquals('dylan', $consistent->name, "name wasn't properly populated");
        $this->assertEquals('user', $consistent->label, "label wasn't properly populated");

        $this->assertEquals('#1:1', $consistent->meta()->rid, "id wasn't properly populated");
        $this->assertEquals('user', $consistent->meta()->oClass, "class wasn't properly populated");
        $this->assertEquals(1, $consistent->meta()->version, "version wasn't properly populated");

        // test multiple results
        $recordOne = new Record();
        $recordOne->setRid(new ID(1, 1));
        $recordOne->setOClass('user');
        $recordOne->setVersion(1);
        $recordOne->setOData([
            'name' => 'dylan',
        ]);

        $recordTwo = new Record();
        $recordTwo->setRid(new ID(2, 2));
        $recordTwo->setOClass('post');
        $recordTwo->setVersion(2);
        $recordTwo->setOData([
            'title' => 'awesome',
        ]);

        $response = [$recordOne, $recordTwo];
        $consistent = $driver->formatAsSet($response);

        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'Set formating did not return Collections');
        $this->assertEquals('#1:1', $consistent[0]->meta()->rid, "id wasn't properly populated");
        $this->assertEquals('user', $consistent[0]->meta()->oClass, "class wasn't properly populated");

        $this->assertEquals('#1:1', $consistent[0]->id, "id wasn't properly populated");
        $this->assertEquals('user', $consistent[0]->label, "label wasn't properly populated");
        $this->assertEquals('dylan', $consistent[0]->name, "name wasn't properly populated");

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1], 'Set formating did not return Collections');
        $this->assertEquals('#2:2', $consistent[1]->meta()->rid, "id wasn't properly populated");
        $this->assertEquals('post', $consistent[1]->meta()->oClass, "class wasn't properly populated");

        $this->assertEquals('#2:2', $consistent[1]->id, "id wasn't properly populated");
        $this->assertEquals('post', $consistent[1]->label, "label wasn't properly populated");
        $this->assertEquals('awesome', $consistent[1]->title, "title wasn't properly populated");
    }

    public function testFormatTree()
    {
        $this->markTestSkipped("Tree is not yet implemented as orient doesn't currently support it");
    }

    /**
     * Check the id and label in Response are protected.
     */
    public function testProtectedResponse()
    {
        $this->specify("it throws an Exception when a modifying protected id", function () {
            $driver = new OrientDriver($this->credentials);
            $driver->open();
            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM #9:3"
            ));
            $consistent = $response->getSet();
            $this->assertEquals("#9:3", $consistent->id, "incorrect id found");
            $this->assertEquals("V", $consistent->label, "incorrect label found");

            $consistent->id = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected label", function () {
            $driver = new OrientDriver($this->credentials);
            $driver->open();
            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM #9:3"
            ));
            $consistent = $response->getSet();
            $this->assertEquals("#9:3", $consistent->id, "incorrect id found");
            $this->assertEquals("V", $consistent->label, "incorrect label found");

            $consistent->label = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected meta", function () {
            $driver = new OrientDriver($this->credentials);
            $driver->open();
            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM #9:3"
            ));
            $consistent = $response->getSet();
            $this->assertEquals("#9:3", $consistent->id, "incorrect id found");
            $this->assertEquals("V", $consistent->label, "incorrect label found");

            $consistent->meta()->rid = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);
    }

    public function testFormatPath()
    {
        $this->markTestSkipped("Tree is not yet implemented as orient doesn't currently support it");
    }
}
