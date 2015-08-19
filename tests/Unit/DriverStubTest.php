<?php
namespace Spider\Test\Unit\Drivers;

use Codeception\Specify;

use Spider\Test\Stubs\DriverStub as Driver;
use Spider\Commands\Command;


class DriverStubTest extends \PHPUnit_Framework_TestCase
{
    use Specify;

    public function testFormatAsSet()
    {
        $this->specify("it formats sets with multiple items correctly", function () {
            $response = [
                [
                    'id' => 1,
                    "label" => "user",
                    "properties"=> [
                        "name" => "marko",
                        "age" => 29
                    ]
                ],
                [
                    'id' => 2,
                    "label" => "user",
                    "properties"=> [
                        "name" => "michael",
                        "age" => 234
                    ]
                ],
                [
                    'id' => 3,
                    "label" => "user",
                    "properties"=> [
                        "name" => "dylan",
                        "age" => 1
                    ]
                ],
            ];

            $driver = new Driver();
            $result = $driver->formatAsSet($response);

            $this->assertEquals(3, count($result), "an incorrect nulmber of elements was found");
            $this->assertInstanceOf('Spider\\Base\\Collection', $result[0], "result format is incorrect, elements should be Collections");
            $this->assertEquals(1, $result[0]->id, "an incorrect id was found.");
            $this->assertEquals("user", $result[0]->label, "an incorrect label was found.");
            $this->assertEquals(1, $result[0]->meta()->id, "an incorrect meta.id was found.");
            $this->assertEquals("user", $result[0]->meta()->label, "an incorrect meta.label was found.");
            $this->assertEquals("marko", $result[0]->name, "an incorrect name was found.");
            $this->assertEquals(29, $result[0]->age, "an incorrect age was found.");

            $this->assertInstanceOf('Spider\\Base\\Collection', $result[1], "result format is incorrect, elements should be Collections");
            $this->assertEquals(2, $result[1]->id, "an incorrect id was found.");
            $this->assertEquals("user", $result[1]->label, "an incorrect label was found.");
            $this->assertEquals(2, $result[1]->meta()->id, "an incorrect meta.id was found.");
            $this->assertEquals("user", $result[1]->meta()->label, "an incorrect meta.label was found.");
            $this->assertEquals("michael", $result[1]->name, "an incorrect name was found.");
            $this->assertEquals(234, $result[1]->age, "an incorrect age was found.");

            $this->assertInstanceOf('Spider\\Base\\Collection', $result[2], "result format is incorrect, elements should be Collections");
            $this->assertEquals(3, $result[2]->id, "an incorrect id was found.");
            $this->assertEquals("user", $result[2]->label, "an incorrect label was found.");
            $this->assertEquals(3, $result[2]->meta()->id, "an incorrect meta.id was found.");
            $this->assertEquals("user", $result[2]->meta()->label, "an incorrect meta.label was found.");
            $this->assertEquals("dylan", $result[2]->name, "an incorrect name was found.");
            $this->assertEquals(1, $result[2]->age, "an incorrect age was found.");
        });

        $this->specify("it formats sets with a single item correctly", function () {
            $response = [
                [
                    'id' => 1,
                    "label" => "user",
                    "properties"=> [
                        "name" => "marko",
                        "age" => 29
                    ]
                ]
            ];

            $driver = new Driver();
            $result = $driver->formatAsSet($response);

            $this->assertInstanceOf('Spider\\Base\\Collection', $result, "result format is incorrect, elements should be Collections");
            $this->assertEquals(1, $result->id, "an incorrect id was found.");
            $this->assertEquals("user", $result->label, "an incorrect label was found.");
            $this->assertEquals(1, $result->meta()->id, "an incorrect meta.id was found.");
            $this->assertEquals("user", $result->meta()->label, "an incorrect meta.label was found.");
            $this->assertEquals("marko", $result->name, "an incorrect name was found.");
            $this->assertEquals(29, $result->age, "an incorrect age was found.");
        });
    }

    public function testProtectedResponse()
    {
        $this->specify("it throws an Exception when modifying protected id", function () {
            $response = [
                [
                    'id' => 1,
                    "label" => "user",
                    "properties"=> [
                        "name" => "marko",
                        "age" => 29
                    ]
                ]
            ];

            $driver = new Driver();
            $consistent = $driver->formatAsSet($response);

            $consistent->id = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected label", function () {
            $response = [
                [
                    'id' => 1,
                    "label" => "user",
                    "properties"=> [
                        "name" => "marko",
                        "age" => 29
                    ]
                ]
            ];

            $driver = new Driver();
            $consistent = $driver->formatAsSet($response);

            $consistent->label = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected meta", function () {
            $response = [
                [
                    'id' => 1,
                    "label" => "user",
                    "properties"=> [
                        "name" => "marko",
                        "age" => 29
                    ]
                ]
            ];

            $driver = new Driver();
            $consistent = $driver->formatAsSet($response);

            $consistent->meta()->id = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new \Michaels\Manager\Exceptions\ModifyingProtectedValueException]);
    }

    public function testFormatAsScalar()
    {
        $response = 36;

        $driver = new Driver();
        $consistent = $driver->formatAsScalar($response);

        $this->assertTrue(is_int($consistent), "did not find the expected scalar format");
        $this->assertEquals(36, $consistent, "did not find the expected scalar output");

        $response = "something";

        $consistent = $driver->formatAsScalar($response);

        $this->assertTrue(is_string($consistent), "did not find the expected scalar format");
        $this->assertEquals("something", $consistent, "did not find the expected scalar output");
    }

    public function testFormatAsPath()
    {
        $this->specify("it formats sets with multiple items correctly", function () {
            $response = [
                [
                    [
                        'id' => 1,
                        "label" => "user",
                        "properties"=> [
                            "name" => "marko",
                            "age" => 29
                        ]
                    ],
                    [
                        'id' => 2,
                        "label" => "user",
                        "properties"=> [
                            "name" => "michael",
                            "age" => 234
                        ]
                    ],
                ],
                [
                    [
                        'id' => 3,
                        "label" => "user",
                        "properties"=> [
                            "name" => "dylan",
                            "age" => 1
                        ]
                    ],
                ]
            ];

            $driver = new Driver();
            $result = $driver->formatAsPath($response);

            $this->assertEquals(2, count($result), "an incorrect number of paths was found");

            $this->assertEquals(2, count($result[0]), "an incorrect number of Elements was found in the first path");
            $this->assertInstanceOf('Spider\\Base\\Collection', $result[0][0], "result format is incorrect, elements should be Collections");
            $this->assertEquals(1, $result[0][0]->id, "an incorrect id was found.");
            $this->assertEquals("user", $result[0][0]->label, "an incorrect label was found.");
            $this->assertEquals(1, $result[0][0]->meta()->id, "an incorrect meta.id was found.");
            $this->assertEquals("user", $result[0][0]->meta()->label, "an incorrect meta.label was found.");
            $this->assertEquals("marko", $result[0][0]->name, "an incorrect name was found.");
            $this->assertEquals(29, $result[0][0]->age, "an incorrect age was found.");

            $this->assertInstanceOf('Spider\\Base\\Collection', $result[0][1], "result format is incorrect, elements should be Collections");
            $this->assertEquals(2, $result[0][1]->id, "an incorrect id was found.");
            $this->assertEquals("user", $result[0][1]->label, "an incorrect label was found.");
            $this->assertEquals(2, $result[0][1]->meta()->id, "an incorrect meta.id was found.");
            $this->assertEquals("user", $result[0][1]->meta()->label, "an incorrect meta.label was found.");
            $this->assertEquals("michael", $result[0][1]->name, "an incorrect name was found.");
            $this->assertEquals(234, $result[0][1]->age, "an incorrect age was found.");


            $this->assertEquals(1, count($result[1]), "an incorrect number of Elements was found in the second path");
            $this->assertInstanceOf('Spider\\Base\\Collection', $result[1][0], "result format is incorrect, elements should be Collections");
            $this->assertEquals(3, $result[1][0]->id, "an incorrect id was found.");
            $this->assertEquals("user", $result[1][0]->label, "an incorrect label was found.");
            $this->assertEquals(3, $result[1][0]->meta()->id, "an incorrect meta.id was found.");
            $this->assertEquals("user", $result[1][0]->meta()->label, "an incorrect meta.label was found.");
            $this->assertEquals("dylan", $result[1][0]->name, "an incorrect name was found.");
            $this->assertEquals(1, $result[1][0]->age, "an incorrect age was found.");
        });
    }
}
