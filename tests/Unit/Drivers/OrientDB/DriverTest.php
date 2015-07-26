<?php
namespace Spider\Test\Unit\Drivers\OrientDB;

use Codeception\Specify;
use Spider\Commands\Command;
use Spider\Drivers\OrientDB\Driver as OrientDriver;

class DriverTest extends \PHPUnit_Framework_TestCase
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
                "SELECT FROM Cat WHERE @rid = #12:0"
            ));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return a Record');
            $this->assertEquals("oreo", $response->name, "failed to return the correct names");
            $this->assertEquals("Cat", $response->label, "failed to return the correct label");
            $this->assertEquals('#12:0', $response->id, "failed to return the correct id");
        });

        $this->specify("it selects multiple unrelated records and returns an array of Records", function () {
            $driver = new OrientDriver($this->credentials);
            $driver->open();

            $response = $driver->executeReadCommand(new Command(
                "SELECT FROM Cat"
            ));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(2, $response, "failed to return 2 results");
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return Response Object');
        });
    }

    public function testWriteCommands()
    {
        $driver = new OrientDriver($this->credentials);
        $driver->open();

        // Create new
        $query = "INSERT INTO Owner CONTENT " . json_encode(['first_name' => 'nicole', 'last_name' => 'lowman']);
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $newRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $newRecord, 'failed to return a Record');
        $this->assertEquals("nicole", $newRecord->first_name, "failed to return the correct names");

        // Update existing
        $query = "UPDATE (SELECT FROM Owner WHERE @rid=$newRecord->id) MERGE " . json_encode(['last_name' => 'wilson']) . ' RETURN AFTER $current';
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $updatedRecord, 'failed to return a Record');
        $this->assertEquals("wilson", $updatedRecord->last_name, "failed to return the correct names");


        // Delete That one
        $query = "DELETE VERTEX Owner WHERE @rid=$newRecord->id";
        $response = $driver->executeWriteCommand(new Command($query));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertEquals([], $updatedRecord, "failed to delete");

        // And try to get it again
        $response = $driver->executeReadCommand(new Command("SELECT FROM Owner WHERE @rid=$newRecord->id"));

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

        $response = [10];
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals(10, $consistent, 'Scalar formating did not properly work with Int');

        $response = ['string'];
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals('string', $consistent, 'Scalar formating did not properly work with String');
    }

//    public function testFormatSet()
//    {
//        $driver = new GremlinDriver();
//
//        // test single result
//        $response = [
//            [
//                'id'=> 430,
//                'label' => 'user',
//                'type' => 'vertex',
//                'properties' => [
//                    'name' => [
//                        [
//                            'id' => 431,
//                            'value' => 'dylan',
//                        ]
//                    ]
//                ],
//            ]
//        ];
//        $consistent = $driver->formatAsSet($response);
//        $this->assertInstanceOf('Spider\Base\Collection', $consistent, 'Set formating did not properly work for single entry');
//        $this->assertEquals(430, $consistent->meta()->id, "id wasn't properly populated");
//        $this->assertEquals('user', $consistent->meta()->label, "label wasn't properly populated");
//        $this->assertEquals('vertex', $consistent->meta()->type, "type wasn't properly populated");
//        $this->assertEquals('dylan', $consistent->name, "name wasn't properly populated");
//
//        // test multiple results
//        $response = [
//            [
//                'id'=> 430,
//                'label' => 'user',
//                'type' => 'vertex',
//                'properties' => [
//                    'name' => [
//                        [
//                            'id' => 431,
//                            'value' => 'dylan',
//                        ]
//                    ]
//                ],
//            ],
//            [
//                'id'=> 480,
//                'label' => 'user',
//                'type' => 'vertex',
//                'properties' => [
//                    'name' => [
//                        [
//                            'id' => 432,
//                            'value' => 'chris',
//                        ]
//                    ]
//                ],
//            ]
//        ];
//        $consistent = $driver->formatAsSet($response);
//        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');
//
//        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'Set formating did not properly work for single entry');
//        $this->assertEquals(430, $consistent[0]->meta()->id, "id wasn't properly populated");
//        $this->assertEquals('user', $consistent[0]->meta()->label, "label wasn't properly populated");
//        $this->assertEquals('vertex', $consistent[0]->meta()->type, "type wasn't properly populated");
//        $this->assertEquals('dylan', $consistent[0]->name, "name wasn't properly populated");
//
//        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1], 'Set formating did not properly work for single entry');
//        $this->assertEquals(480, $consistent[1]->meta()->id, "id wasn't properly populated");
//        $this->assertEquals('user', $consistent[1]->meta()->label, "label wasn't properly populated");
//        $this->assertEquals('vertex', $consistent[1]->meta()->type, "type wasn't properly populated");
//        $this->assertEquals('chris', $consistent[1]->name, "name wasn't properly populated");
//
//    }
//
//    public function testFormatPath()
//    {
//        $driver = new GremlinDriver();
//
//        // test single result
//        $response = [
//            [
//                'labels'=> [[],[]],
//                'objects' => [
//                    [
//                        'id'=> 430,
//                        'label' => 'user',
//                        'type' => 'vertex',
//                        'properties' => [
//                            'name' => [
//                                [
//                                    'id' => 431,
//                                    'value' => 'dylan',
//                                ]
//                            ]
//                        ],
//                    ],
//                    [
//                        'id'=> 480,
//                        'label' => 'user',
//                        'type' => 'vertex',
//                        'properties' => [
//                            'name' => [
//                                [
//                                    'id' => 432,
//                                    'value' => 'chris',
//                                ]
//                            ]
//                        ],
//                    ]
//                ]
//            ],
//            [
//                'labels'=> [[],[]],
//                'objects' => [
//                    [
//                        'id'=> 480,
//                        'label' => 'user',
//                        'type' => 'vertex',
//                        'properties' => [
//                            'name' => [
//                                [
//                                    'id' => 432,
//                                    'value' => 'chris',
//                                ]
//                            ]
//                        ],
//                    ],
//                    [
//                        'id'=> 430,
//                        'label' => 'user',
//                        'type' => 'vertex',
//                        'properties' => [
//                            'name' => [
//                                [
//                                    'id' => 431,
//                                    'value' => 'dylan',
//                                ]
//                            ]
//                        ],
//                    ],
//                ]
//            ]
//        ];
//        $consistent = $driver->formatAsPath($response);
//        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');
//
//        //First path
//        $this->assertTrue(is_array($consistent[0]), 'the formatted response first path is not an array');
//
//        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][0], 'Path formating did not properly work for single entry');
//        $this->assertEquals(430, $consistent[0][0]->meta()->id, "id wasn't properly populated");
//        $this->assertEquals('user', $consistent[0][0]->meta()->label, "label wasn't properly populated");
//        $this->assertEquals('vertex', $consistent[0][0]->meta()->type, "type wasn't properly populated");
//        $this->assertEquals('dylan', $consistent[0][0]->name, "name wasn't properly populated");
//
//        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0][1], 'Path formating did not properly work for single entry');
//        $this->assertEquals(480, $consistent[0][1]->meta()->id, "id wasn't properly populated");
//        $this->assertEquals('user', $consistent[0][1]->meta()->label, "label wasn't properly populated");
//        $this->assertEquals('vertex', $consistent[0][1]->meta()->type, "type wasn't properly populated");
//        $this->assertEquals('chris', $consistent[0][1]->name, "name wasn't properly populated");
//
//        //Second Path
//        $this->assertTrue(is_array($consistent[1]), 'the formatted response second path is not an array');
//
//        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1][0], 'Path formating did not properly work for single entry');
//        $this->assertEquals(480, $consistent[1][0]->meta()->id, "id wasn't properly populated");
//        $this->assertEquals('user', $consistent[1][0]->meta()->label, "label wasn't properly populated");
//        $this->assertEquals('vertex', $consistent[1][0]->meta()->type, "type wasn't properly populated");
//        $this->assertEquals('chris', $consistent[1][0]->name, "name wasn't properly populated");
//
//        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1][1], 'Path formating did not properly work for single entry');
//        $this->assertEquals(430, $consistent[1][1]->meta()->id, "id wasn't properly populated");
//        $this->assertEquals('user', $consistent[1][1]->meta()->label, "label wasn't properly populated");
//        $this->assertEquals('vertex', $consistent[1][1]->meta()->type, "type wasn't properly populated");
//        $this->assertEquals('dylan', $consistent[1][1]->name, "name wasn't properly populated");
//
//    }
//
//    public function testFormatTree()
//    {
//        $this->markTestSkipped("Tree is not yet implemented as gremlin-server doesn't curently support it");
//    }
//
//    /**
//     * Check the id and label in Response are protected.
//     */
//    public function testProtectedResponse()
//    {
//        $this->specify("it throws an Exception when a modifying protected id", function () {
//            $driver = new GremlinDriver($this->credentials);
//            $driver->open();
//            $response = $driver->executeReadCommand(new Command(
//                $driver->traversal.".V().has('name', 'marko').limit(1)"
//            ));
//            $consistent = $response->getSet();
//            $this->assertEquals(1, $consistent->id, "incorrect id found");
//            $this->assertEquals("vertex", $consistent->label, "incorrect label found");
//
//            $consistent->id = 100; // should throw an error
//
//            $driver->close();
//        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);
//
//        $this->specify("it throws an Exception when a modifying protected label", function () {
//            $driver = new GremlinDriver($this->credentials);
//            $driver->open();
//            $response = $driver->executeReadCommand(new Command(
//                $driver->traversal.".V().has('name', 'marko').limit(1)"
//            ));
//            $consistent = $response->getSet();
//            $this->assertEquals(1, $consistent->id, "incorrect id found");
//            $this->assertEquals("vertex", $consistent->label, "incorrect label found");
//
//            $consistent->label = 100; // should throw an error
//
//            $driver->close();
//        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);
//
//        $this->specify("it throws an Exception when a modifying protected meta", function () {
//            $driver = new GremlinDriver($this->credentials);
//            $driver->open();
//            $response = $driver->executeReadCommand(new Command(
//                $driver->traversal.".V().has('name', 'marko').limit(1)"
//            ));
//            $consistent = $response->getSet();
//            $this->assertEquals(1, $consistent->id, "incorrect id found");
//            $this->assertEquals("vertex", $consistent->label, "incorrect label found");
//
//            $consistent->meta()->id = 100; // should throw an error
//
//            $driver->close();
//        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);
//    }
}
