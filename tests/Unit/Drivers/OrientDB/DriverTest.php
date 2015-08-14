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
        //$this->markTestSkipped("Test Database Not Installed");
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
            'database' => 'GratefulDeadConcerts'
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
            'command' => new Command("SELECT FROM V WHERE @rid = #9:1", "orientSQL"),
            'expected' => [
                [
                    'id' => '#9:1',
                    'name' => 'HEY BO DIDDLEY',
                    'label' => 'V'
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
                "SELECT FROM V WHERE song_type = 'cover' LIMIT 2", "orientSQL"
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
     * Command selects exactly one record by name = $name
     * Expected: Not used. Return an empty array
     * @param $name
     * @return array
     */
    public function selectByName($name)
    {
        return [
            'command' => new Command("SELECT FROM V WHERE name = '$name'", "orientSQL"),
            'expected' => [],
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
            'command' => new Command(
                "CREATE Vertex CONTENT " . json_encode(['name' => 'testVertex']), "orientSQL"
            ),
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
        $query = "UPDATE (SELECT FROM V WHERE name='$name') ";
        $query .= "MERGE " . json_encode(['name' => 'testVertex2']) . ' RETURN AFTER $current';

        return [
            'command' => new Command($query, "orientSQL"),
            'expected' => [
                [
                    'name' => 'testVertex2',
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
        return [
            'command' => new Command("DELETE VERTEX WHERE name = '$name'", "orientSQL"),
            'expected' => []
        ];
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
    }

    /* Orient Specific Tests */
    public function testBuildTransactionStatement()
    {
        $this->specify("it builds a correct transaction", function () {
            $driver = $this->driver();
            $driver->open();
            $driver->startTransaction();

            $driver->executeWriteCommand(new Command(
                "CREATE VERTEX CONTENT {name:'one'}", "orientSQL"
            ));

            $driver->executeWriteCommand(new Command(
                "CREATE VERTEX CONTENT {name:'two'}", "orientSQL"
            ));

            $driver->executeWriteCommand(new Command(
                "CREATE VERTEX CONTENT {name:'three'}", "orientSQL"
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

    public function testPassingBuilder()
    {
        $builder = new \Spider\Commands\Builder();
        $builder->select()->from('V');
        $driver = $this->driver();
        $driver->open();

        $response = $driver->executeReadCommand($builder);

        $consistent = $response->getSet();
        $this->assertEquals(20, count($consistent), "wrong number of elements found");
    }
}
