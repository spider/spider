<?php
namespace Spider\Test\Unit\Drivers;

use Codeception\Specify;
use Michaels\Manager\Exceptions\ModifyingProtectedValueException;
use Spider\Commands\Command;
use Spider\Drivers\DriverInterface;
use Spider\Exceptions\FormattingException;
use Spider\Exceptions\InvalidCommandException;
use Spider\Exceptions\NotSupportedException;

/**
 * This is the base tests for all driver.
 *
 * Each driver should extend this class and implement the required methods.
 * This ensures that all drivers meet the same testing requirements.
 *
 * The methods each driver implements will return a Command that executes a query
 * and an array of expected results. Each docblock specifies which results will be tested.
 *
 * Please be sure that the expected results match those returned from the test database
 *
 * Even when only one result is retrieved from the database. The 'expected' array should be
 * a two-level array `'expected' => [
 *      [
 *          'name' => 'first result'
 *      ],
 * ];
 *
 * See the existing drivers for more information.
 */
abstract class BaseTestSuite extends \PHPUnit_Framework_TestCase
{
    use Specify;

    protected $fixture;
    protected $expected = [
        [
            'label' => 'person',
            'name' => 'marko',
            'age' => 29,
            'out' => [
                [
                    'label' => 'knows',
                    'weight' => 0.5,
                    'to' => 1
                ],
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'to' => 4
                ],
            ]
        ],
        [
            'label' => 'person',
            'name' => 'vadas',
            'age' => 27,
            'in' => [
                [
                    'label' => 'knows',
                    'weight' => 0.5,
                    'from' => 0
                ],
            ]
        ],
        [
            'label' => 'person',
            'name' => 'peter',
            'age' => 35,
            'out' => [
                [
                    'label' => 'created',
                    'weight' => 0.2,
                    'to' => 4
                ],
            ]
        ],
        [
            'label' => 'person',
            'name' => 'josh',
            'age' => 32,
            'out' => [
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'to' => 4
                ],
                [
                    'label' => 'created',
                    'weight' => 1.0,
                    'to' => 5
                ],
            ]
        ],
        [
            'label' => 'person',
            'name' => 'lop',
            'lang' => 'java',
            'in' => [
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'from' => 0
                ],
                [
                    'label' => 'created',
                    'weight' => 0.2,
                    'from' => 2
                ],
                [
                    'label' => 'created',
                    'weight' => 0.4,
                    'from' => 3
                ]
            ]
        ],
        [
            'label' => 'person',
            'name' => 'ripple',
            'lang' => 'java',
            'in' => [
                [
                    'label' => 'created',
                    'weight' => 1.0,
                    'from' => 3
                ],
            ],
        ]
    ];

    /* Begin Tests */
    public function testConnections()
    {
        $this->specify("it opens and closes the database without exception", function () {
            $driver = $this->driver();
            $driver->open();
            $driver->close();
        });
    }

    public function testReadCommands()
    {
        $this->specify("it selects a single vertex", function () {
            $driver = $this->driver();
            $driver->open();

            $response = $driver->executeCommand($this->getCommand('select-one-item'));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return a Record');

            $this->assertEquals(
                $this->expected[0]['name'],
                $response->name,
                "failed to return the correct names"
            );

            $this->assertEquals(
                $this->expected[0]['label'],
                $response->label,
                "failed to return the correct label"
            );
        });

        $this->specify("it selects multiple, unrelated vertices", function () {
            $driver = $this->driver();
            $driver->open();

            $response = $driver->executeCommand($this->getCommand('select-two-items'));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(2, $response, "failed to return 2 results");
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return Response Object');
            $this->assertEquals(
                $this->expected[1]['name'],
                $response[1]->name,
                "failed to return the correct record"
            );
        });
    }

    public function testWriteCommands()
    {
        $driver = $this->driver();
        $driver->open();

        // Create new
        $response = $driver->executeCommand($this->getCommand('create-one-item'));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $newRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $newRecord, 'failed to return a Record');
        $this->assertEquals(
            'testVertex',
            $newRecord->name,
            "failed to return the correct names"
        );

        // Update existing
        $response = $driver->executeCommand($this->getCommand('update-one-item', $newRecord->name));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $updatedRecord, 'failed to return a Record');
        $this->assertEquals(
            'testVertex2',
            $updatedRecord->name,
            "failed to return the correct names"
        );

        // Delete That one
        $response = $driver->executeCommand($this->getCommand('delete-one-item', $updatedRecord->name));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $deletedRecord = $response->getSet();

        $this->assertEquals([], $deletedRecord, "failed to delete");

        // And try to get it again
        $response = $driver->executeCommand(
            $this->getCommand('select-by-name', $updatedRecord->name)
        );

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $response = $response->getSet();

        $this->assertTrue(is_array($response), 'failed to return an array');
        $this->assertEmpty($response, "failed to return an EMPTY array");

        // Done
        $driver->close();
    }

    public function testTransactions()
    {
        $this->specify("it rollbacks properly on transactional graph", function () {
            $driver = $this->driver('transaction');
            $driver->open();
            $driver->startTransaction();

            $driver->executeCommand($this->getCommand('create-one-item'));

            $driver->stopTransaction(false);

            // Try to get that non-existent vertex
            $response = $driver->executeCommand(
                $this->getCommand(
                    'select-by-name',
                    'testVertex'
                )
            );
            $consistent = $response->getSet();

            // Should be an empty array if transaction did not commit
            $this->assertTrue(is_array($consistent), 'failed to return an array');
            $this->assertEmpty($consistent, "failed to return an EMPTY array");
            $driver->close();
        });

        $this->specify("it commits properly on transactional graph", function () {
            $driver = $this->driver('transaction');
            $driver->open();
            $driver->startTransaction();

            $driver->executeCommand($this->getCommand('create-one-item'));

            $driver->stopTransaction(true);

            // Get the item just created
            $response = $driver->executeCommand(
                $this->getCommand(
                    'select-by-name',
                    'testVertex'
                )
            );
            $response = $response->getSet();

            $this->assertEquals(
                'testVertex',
                $response->name,
                "the commit did not properly work"
            );

            // Clean up
            $driver->runWriteCommand(
                $this->getCommand(
                    'delete-one-item',
                    'testVertex'
                )
            );

            $driver->close();
        });

        $this->specify("it throws an Exception on multiple transaction", function () {
            $driver = $this->driver('transaction');
            $driver->open();
            $driver->startTransaction();
            $driver->startTransaction();
            $driver->close();
        }, ['throws' => new InvalidCommandException()]);

        $this->specify("it throws an Exception when a non existing transaction is stopped", function () {
            $driver = $this->driver('transaction');
            $driver->open();
            $driver->stopTransaction();
            $driver->close();
        }, ['throws' => new InvalidCommandException()]);
    }

    public function testFormatScalar()
    {
        $driver = $this->driver();

        // solo int, string, bool
        $response = $this->getScalarResponse('int');
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals(10, $consistent, 'Scalar formatting did not properly work with Int');

        $response = $this->getScalarResponse('string');
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals('string', $consistent, 'Scalar formatting did not properly work with String');

        $response = $this->getScalarResponse('boolean');
        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals(true, $consistent, 'Scalar formatting did not properly work with Bool');
    }

    public function testThrowsFormattingExceptionForScalar()
    {
        $this->specify("it throws an exception for single set response on scalar formatting", function () {
            $driver = $this->driver();

            $response = [$this->expected[0]];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);

        $this->specify("it throws an exception for a multi set response on scalar formatting", function () {
            $driver = $this->driver();

            $response[] = $this->expected[0];
            $response[] = $this->expected[1];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);
    }

    public function testThrowsFormattingExceptionForSet()
    {
        $this->specify("it throws an exception for scalar response on set formatting", function () {
            $driver = $this->driver();

            $response = $this->getScalarResponse('string');

            $driver->formatAsSet($response);
        }, ['throws' => new FormattingException()]);

    }

    public function testFormatSet()
    {
        $this->specify("it formats a single record as a Collection", function () {
            $driver = $this->driver();
            $driver->open();

            $rawResponse = $driver->executeCommand(
                $this->getCommand('select-one-item')
            )->getRaw();

            $driver->close();

            $consistent = $driver->formatAsSet($rawResponse);
            $this->assertInstanceOf('Spider\Base\Collection', $consistent, 'Did not return a single Collection');
            $this->assertEquals(
                $this->expected[0]['name'],
                $consistent->name,
                "name wasn't properly populated"
            );
            $this->assertEquals(
                $this->expected[0]['label'],
                $consistent->label,
                "label wasn't properly populated"
            );
            $this->assertTrue(is_array($consistent->meta), 'failed to populate meta');
        });

        $this->specify("it formats a multiple records as an array of Collections", function () {
            $driver = $this->driver();
            $driver->open();

            $rawResponse = $driver->executeCommand(
                $this->getCommand('select-two-items')
            )->getRaw();

            $driver->close();

            $consistent = $driver->formatAsSet($rawResponse);

            $this->assertTrue(is_array($consistent), 'the formatted response is not an array');

            $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'first: Did not return a collection');
            $this->assertTrue(is_array($consistent[0]->meta), 'first: failed to populate meta');
            $this->assertEquals(
                $this->expected[0]['label'],
                $consistent[0]->label,
                "first label wasn't properly populated"
            );
            $this->assertEquals(
                $this->expected[0]['name'],
                $consistent[0]->name,
                "first name wasn't properly populated"
            );

            $this->assertInstanceOf('Spider\Base\Collection', $consistent[1], 'second: Did not return a collection');
            $this->assertTrue(is_array($consistent[1]->meta), 'second: failed to populate meta');
            $this->assertEquals(
                $this->expected[1]['label'],
                $consistent[1]->label,
                "second: label wasn't properly populated"
            );
            $this->assertEquals(
                $this->expected[1]['name'],
                $consistent[1]->name,
                "second: name wasn't properly populated"
            );
        });
    }

    /**
     * Check the id and label in Response are protected.
     */
    public function testProtectedResponse()
    {
        $this->specify("it throws an Exception when a modifying protected id", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();

            $this->assertEquals(
                $this->expected[0]['label'],
                $consistent->label,
                "incorrect label found"
            );
            $consistent->id = 100; // should throw an error

            $driver->close();
        }, ['throws' => new ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected label", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();

            $this->assertEquals(
                $this->expected[0]['label'],
                $consistent->label,
                "incorrect label found"
            );

            $consistent->label = 100; // should throw an error

            $driver->close();
        }, ['throws' => new ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected meta", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();

            $this->assertEquals(
                $this->expected[0]['label'],
                $consistent->label,
                "incorrect label found"
            );

            $metaKey = $this->getMetaKey();
            $consistent->meta()->$metaKey = 100; // should throw an error

            $driver->close();
        }, ['throws' => new ModifyingProtectedValueException]);
    }

    public function testIncorrectLanguage()
    {
        $this->specify("it throws an Exception when a command with an unknown language is submitted", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeCommand(new Command('script', 'unknown-language'));
        }, ['throws' => new NotSupportedException]);
    }

    public function testIncorrectScript()
    {
        $this->setExpectedException('Exception');

        $driver = $this->driver();
        $driver->open();
        $command = $this->getCommand('select-one-item');
        $command->setScript('incorrect-script');
        $response = $driver->executeCommand($command);
    }

    /* Internal Methods */
    protected function camelCase($alias)
    {
        $key = str_replace("-", " ", $alias);
        $key = ucwords($key);
        $key = str_replace(" ", "", $key);
        $key = lcfirst($key);
        return $key;
    }

    protected function getCommand($alias, $arg = null)
    {
        $method = $this->camelCase($alias);
        return $this->$method($arg);
    }

    /* Queries to Implement */
    /** Returns an instance of the configured driver
     * @param null $switch
     * @return DriverInterface
     */
    abstract public function driver($switch = null);

    /**
     * Command selects exactly one record from "person"
     * @return Command
     */
    abstract public function selectOneItem();

    /**
     * Command selects exactly the first two records from "person"
     * @return Command
     */
    abstract public function selectTwoItems();

    /**
     * Command selects exactly one record by name = $name
     * @return Command
     */
    abstract public function selectByName($name);

    /**
     * Command creates a single record with the name "testVertex"
     * @return Command
     */
    abstract public function createOneItem();

    /**
     * Command updates a single item by name = ?, changing the name to "testVertex2"
     * @param $name
     * @return Command
     */
    abstract public function updateOneItem($name);

    /**
     * Command deletes a single item by name = ?
     * @param $name
     * @return Command
     */
    abstract public function deleteOneItem($name);

    /**
     * Returns the name of a meta property used by the driver
     * @return string
     */
    abstract public function getMetaKey();

    /**
     * Returns the response needed to formatAsScalar()
     * Must switch between int, string, boolean
     * @param $type
     * @return array
     */
    abstract public function getScalarResponse($type);
}
