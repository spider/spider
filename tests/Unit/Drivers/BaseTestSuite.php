<?php
namespace Spider\Test\Unit\Drivers;

use Codeception\Specify;
use Michaels\Manager\Exceptions\ModifyingProtectedValueException;
use Spider\Exceptions\FormattingException;
use Spider\Exceptions\InvalidCommandException;

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

            $response = $driver->executeReadCommand($this->getCommand('select-one-item'));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();
            $this->assertInstanceOf('Spider\Base\Collection', $response, 'failed to return a Record');

            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['name'],
                $response->name,
                "failed to return the correct names"
            );

            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['label'],
                $response->label,
                "failed to return the correct label"
            );

            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['id'],
                $response->id,
                "failed to return the correct id"
            );
        });

        $this->specify("it selects multiple, unrelated vertices", function () {
            $driver = $this->driver();
            $driver->open();

            $response = $driver->executeReadCommand($this->getCommand('select-two-items'));

            $driver->close();

            $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
            $response = $response->getSet();

            $this->assertTrue(is_array($response), "failed to return an array");
            $this->assertCount(2, $response, "failed to return 2 results");
            $this->assertInstanceOf('Spider\Base\Collection', $response[0], 'failed to return Response Object');
            $this->assertEquals(
                $this->getExpected('select-two-items')[1]['name'],
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
        $response = $driver->executeWriteCommand($this->getCommand('create-one-item'));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $newRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $newRecord, 'failed to return a Record');
        $this->assertEquals(
            $this->getExpected('create-one-item')[0]['name'],
            $newRecord->name,
            "failed to return the correct names"
        );

        // Update existing
        $response = $driver->executeWriteCommand($this->getCommand('update-one-item', $newRecord->name));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $updatedRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $updatedRecord, 'failed to return a Record');
        $this->assertEquals(
            $this->getExpected('update-one-item')[0]['name'],
            $updatedRecord->name,
            "failed to return the correct names"
        );

        // Delete That one
        $response = $driver->executeWriteCommand($this->getCommand('delete-one-item', $updatedRecord->name));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $deletedRecord = $response->getSet();

        $this->assertEquals($this->getExpected('delete-one-item'), $deletedRecord, "failed to delete");

        // And try to get it again
        $response = $driver->executeReadCommand(
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

            $driver->executeWriteCommand($this->getCommand('create-one-item'));

            $driver->stopTransaction(false);

            // Try to get that non-existent vertex
            $response = $driver->executeReadCommand(
                $this->getCommand(
                    'select-by-name',
                    $this->getExpected('create-one-item')[0]['name']
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

            $driver->executeWriteCommand($this->getCommand('create-one-item'));

            $driver->stopTransaction(true);

            // Get the item just created
            $response = $driver->executeReadCommand(
                $this->getCommand(
                    'select-by-name',
                    $this->getExpected('create-one-item')[0]['name']
                )
            );
            $response = $response->getSet();

            $this->assertEquals(
                $this->getExpected('create-one-item')[0]['name'],
                $response->name,
                "the commit did not properly work"
            );

            // Clean up
            $driver->runWriteCommand(
                $this->getCommand(
                    'delete-one-item',
                    $this->getExpected('create-one-item')[0]['name']
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

        /* Scalar should format a single record with a single value as a scalar */
        /*
        $record= new Record();
        $record->setOData(['item' => 10]);
        $response = [$record];

        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals(10, $consistent, 'Scalar formatting did not properly work with Record Int');

         Record with string
        $record = new Record();
        $record->setOData(['item' => 'string']);
        $response = [$record];

        $consistent = $driver->formatAsScalar($response);
        $this->assertEquals('string', $consistent, 'Scalar formatting did not properly work with Record String');
        */
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
        /* Should throw an exception for a record with more than one item */
        /*
        $this->specify("it throws an exception for record with more than one item", function () {
            $driver = $this->driver();

            $record = new Record();
            $record->setOData(['item' => 10, 'two' => 2]);
            $response = [$record];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);

        $this->specify("it throws an exception for multiple records", function () {
            $driver = $this->driver();

            $record = new Record();
            $another = new Record();
            $response = [$record, $another];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);
        */

        $this->specify("it throws an exception for multiple scalar values", function () {
            $driver = $this->driver();

            $response = [1, 2];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);

        $this->specify("it throws an exception for a non-array", function () {
            $driver = $this->driver();

            $response = 3;

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);

        $this->specify("it throws an exception for an array of invalid objects", function () {
            $driver = $this->driver();

            $response = [[1]];

            $driver->formatAsScalar($response);
        }, ['throws' => new FormattingException()]);
    }

    public function testFormatSet()
    {
        $this->specify("it formats a single record as a Collection", function () {
            $driver = $this->driver();
            $driver->open();

            $rawResponse = $driver->executeReadCommand(
                $this->getCommand('select-one-item')
            )->getRaw();

            $driver->close();

            $consistent = $driver->formatAsSet($rawResponse);
            $this->assertInstanceOf('Spider\Base\Collection', $consistent, 'Did not return a single Collection');
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['name'],
                $consistent->name,
                "name wasn't properly populated"
            );
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['label'],
                $consistent->label,
                "label wasn't properly populated"
            );
            $this->assertTrue(is_array($consistent->meta), 'failed to populate meta');
        });

        $this->specify("it formats a multiple records as an array of Collections", function () {
            $driver = $this->driver();
            $driver->open();

            $rawResponse = $driver->executeReadCommand(
                $this->getCommand('select-two-items')
            )->getRaw();

            $driver->close();

            $consistent = $driver->formatAsSet($rawResponse);

            $this->assertTrue(is_array($consistent), 'the formatted response is not an array');

            $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'first: Did not return a collection');
            $this->assertTrue(is_array($consistent[0]->meta), 'first: failed to populate meta');
            $this->assertEquals(
                $this->getExpected('select-two-items')[0]['label'],
                $consistent[0]->label,
                "first label wasn't properly populated"
            );
            $this->assertEquals(
                $this->getExpected('select-two-items')[0]['name'],
                $consistent[0]->name,
                "first name wasn't properly populated"
            );

            $this->assertInstanceOf('Spider\Base\Collection', $consistent[1], 'second: Did not return a collection');
            $this->assertTrue(is_array($consistent[1]->meta), 'second: failed to populate meta');
            $this->assertEquals(
                $this->getExpected('select-two-items')[1]['label'],
                $consistent[1]->label,
                "second: label wasn't properly populated"
            );
            $this->assertEquals(
                $this->getExpected('select-two-items')[1]['name'],
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
            $response = $driver->executeReadCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['id'],
                $consistent->id,
                "incorrect id found"
            );
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['label'],
                $consistent->label,
                "incorrect label found"
            );
            $consistent->id = 100; // should throw an error

            $driver->close();
        }, ['throws' => new ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected label", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeReadCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['id'],
                $consistent->id,
                "incorrect id found"
            );
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['label'],
                $consistent->label,
                "incorrect label found"
            );

            $consistent->label = 100; // should throw an error

            $driver->close();
        }, ['throws' => new ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected meta", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeReadCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['id'],
                $consistent->id,
                "incorrect id found"
            );
            $this->assertEquals(
                $this->getExpected('select-one-item')[0]['label'],
                $consistent->label,
                "incorrect label found"
            );

            $metaKey = $this->getMetaKey();
            $consistent->meta()->$metaKey = 100; // should throw an error

            $driver->close();
        }, ['throws' => new ModifyingProtectedValueException]);
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
        return $this->$method($arg)['command'];
    }

    protected function getExpected($alias)
    {
        $method = $this->camelCase($alias);
        return $this->$method(null)['expected'];
    }

    /* Queries to Implement */
    /** Returns an instance of the configured driver */
    abstract public function driver();

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
    abstract public function selectOneItem();

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
    abstract public function selectTwoItems();

    /**
     * Command selects exactly one record by name = $name
     * Expected: Not used. Return an empty array
     * @param $name
     * @return array
     */
    abstract public function selectByName($name);

    /**
     * Command creates a single record with a name
     * Expected: a single array with: `name` created
     * @return array
     */
    abstract public function createOneItem();

    /**
     * Command updates a single item by name = ?, changing the name
     * Expected: a single array with: name
     * @param $name
     * @return array
     */
    abstract public function updateOneItem($name);

    /**
     * Command deletes a single item by name = ?
     * Expected: an empty array
     * @param $name
     * @return array
     */
    abstract public function deleteOneItem($name);

    /**
     * Returns the name of a meta property used by the driver
     * @return string
     */
    abstract public function getMetaKey();
}
