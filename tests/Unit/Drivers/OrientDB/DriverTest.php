<?php
namespace Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;
use Spider\Commands\Command;
use Spider\Drivers\OrientDB\Driver as OrientDriver;
use Spider\Test\Unit\Drivers\DriversTestBase;

class DriverTest extends DriversTestBase
{
    /** Returns an instance of the configured driver */
    public function driver()
    {
        return new OrientDriver([
            'hostname' => 'localhost',
            'port' => 2424,
            'username' => 'root',
            'password' => "root",
            'database' => 'GratefulDeadConcerts'
        ]);
    }

    /**
     * Command selects exactly one record
     * Expected is a single array with: id, name, label
     * @return array
     */
    public function selectOneItem()
    {
        return [
            'command' => new Command("SELECT FROM V WHERE @rid = #9:1"),
            'expected' => [
                'id' => '#9:1',
                'name' => 'HEY BO DIDDLEY',
                'label' => 'V'
            ]
        ];
    }

    /**
     * Command selects exactly two records
     * Expected is two arrays, each with: id, name, label
     * @return array
     */
    public function selectTwoItems()
    {
        return [
            'command' => new Command(
                "SELECT FROM V WHERE song_type = 'cover' LIMIT 2"
            ),
            'expected' => [
                [
                    'id' => '#9:1',
                    'name' => 'HEY BO DIDDLEY',
                    'label' => 'V',
                ],
                [
                    'id' => '#9:2',
                    'name' => 'IM A MAN',
                    'label' => 'V',
                ],
            ]
        ];
    }

    /**
     * Command selects exactly one record by id that does not exist
     * Expected is an empty array
     * @param $id
     * @return array
     */
    public function selectDeletedItem($id)
    {
        return [
            'command' => new Command("SELECT FROM $id"),
            'expected' => []
        ];
    }

    /**
     * Command creates a single record with a name
     * Expected is a single array with: name, label
     * @return array
     */
    public function createOneItem()
    {
        return [
            'command' => new Command(
                "CREATE Vertex CONTENT " . json_encode(['song_type' => 'cover', 'name' => 'New Song'])
            ),
            'expected' => [
                'name' => 'New Song',
                'label' => 'V',
            ]
        ];
    }

    /**
     * Command updates a single item by id, changing the name
     * Expected is a single array with: name, label
     * @param $id
     * @return array
     */
    public function updateOneItem($id)
    {
        $query = "UPDATE (SELECT FROM V WHERE @rid=$id) ";
        $query .= "MERGE " . json_encode(['name' => 'Updated Song']) . ' RETURN AFTER $current';

        return [
            'command' => new Command($query),
            'expected' => [
                'name' => 'Updated Song',
                'label' => 'V',
            ]
        ];
    }

    /**
     * Command deletes a single item by id
     * Expected is an empty array
     * @param $id
     * @return array
     */
    public function deleteOneItem($id)
    {
        return [
            'command' => new Command("DELETE VERTEX $id"),
            'expected' => []
        ];
    }

    /**
     * Command creates a single item with: name
     *
     * The name set must be predictable so
     * it can be deleted in the other transaction properties
     *
     * Expected is a single array with: label, name
     * @return array
     */
    public function createTransactionItem()
    {
        return [
            'command' => new Command(
                "CREATE Vertex CONTENT " . json_encode(['song_type' => 'cover', 'name' => 'testVertex'])
            ),
            'expected' => [
                'label' => 'V',
                'name' => 'testVertex'
            ]
        ];
    }

    /**
     * Command selects the single item created in `createTransactionItem()`
     * Expected is a single array with: name
     * @return array
     */
    public function selectTransactionItem()
    {
        return [
            'command' => new Command(
                "SELECT FROM V WHERE name = 'testVertex'"
            ),
            'expected' => [
                'name' => 'testVertex'
            ]
        ];
    }

    /**
     * Command deletes the item created by `createTransactionItem()`
     * Expected is an empty array
     * @return array
     */
    public function deleteTransactionItem()
    {
        return [
            'command' => new Command(
                "DELETE VERTEX WHERE name = 'testVertex'"
            ),
            'expected' => []
        ];
    }

    /**
     * Builds and returns an array with a single record in
     * the driver's native response format
     * @return array
     */
    public function buildSingleRawResponse()
    {
        $record = new Record();
        $record->setRid(new ID(1, 1));
        $record->setOClass('user');
        $record->setVersion(1);
        $record->setOData([
            'name' => 'dylan',
        ]);

        return [$record];
    }

    /**
     * Builds and returns an array with two records in
     * the driver's native response format
     * @return array
     */
    public function buildTwoRawResponses()
    {
        // test single result
        $one = new Record();
        $one->setRid(new ID(1, 1));
        $one->setOClass('user');
        $one->setVersion(1);
        $one->setOData([
            'name' => 'dylan',
        ]);

        $two = new Record();
        $two->setRid(new ID(2, 2));
        $two->setOClass('user');
        $two->setVersion(2);
        $two->setOData([
            'name' => 'nicole',
        ]);

        return [$one, $two];
    }

    /**
     * Returns the name of a meta property used by the driver
     * @return string
     */
    public function getMetaKey()
    {
        return 'rid';
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
