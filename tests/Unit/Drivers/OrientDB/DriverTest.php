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

    public function readOneItem()
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

    public function readTwoItems()
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

    public function createOneItem()
    {
        return [
            'command' => new Command(
                "CREATE Vertex CONTENT " . json_encode(['song_type' => 'cover', 'name' => 'New Song'])
            ),
            'expected' => [
                'name' => 'New Song',
                'song_gype' => 'cover',
                'label' => 'V',
            ]
        ];
    }

    public function updateOneItem($id)
    {
        $query = "UPDATE (SELECT FROM V WHERE @rid=$id) ";
        $query .= "MERGE " . json_encode(['name' => 'Updated Song']) . ' RETURN AFTER $current';

        return [
            'command' => new Command($query),
            'expected' => [
                'name' => 'Updated Song',
                'song_type' => 'cover',
                'label' => 'V',
            ]
        ];
    }

    public function deleteOneItem($id)
    {
        return [
            'command' => new Command("DELETE VERTEX $id"),
            'expected' => []
        ];
    }

    public function readOneItemById($id)
    {
        return [
            'command' => new Command("SELECT FROM $id"),
            'expected' => []
        ];
    }

    public function createTransactionItem()
    {
        return [
            'command' => new Command(
                "CREATE Vertex CONTENT " . json_encode(['song_type' => 'cover', 'name' => 'testVertex'])
            ),
            'expected' => []
        ];
    }

    public function readTransactionItem()
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

    public function deleteTransactionItem()
    {
        return [
            'command' => new Command(
                "DELETE VERTEX WHERE name = 'testVertex'"
            ),
            'expected' => []
        ];
    }

    public function buildSingleRawResponse()
    {
        // test single result
        $record = new Record();
        $record->setRid(new ID(1, 1));
        $record->setOClass('user');
        $record->setVersion(1);
        $record->setOData([
            'name' => 'dylan',
        ]);

        return [$record];
    }

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

    public function testFormatTree()
    {
        $this->markTestSkipped("Tree is not yet implemented as orient doesn't currently support it");
    }

    public function testFormatPath()
    {
        $this->markTestSkipped("Path is not yet implemented as orient doesn't currently support it");
    }
}
