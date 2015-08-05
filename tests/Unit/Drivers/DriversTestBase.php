<?php
namespace Spider\Test\Unit\Drivers;

use Codeception\Specify;
use Michaels\Manager\Exceptions\ModifyingProtectedValueException;
use Spider\Exceptions\FormattingException;
use Spider\Exceptions\InvalidCommandException;

abstract class DriversTestBase extends \PHPUnit_Framework_TestCase
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
                $this->getExpected('select-one-item')['name'],
                $response->name,
                "failed to return the correct names"
            );

            $this->assertEquals(
                $this->getExpected('select-one-item')['label'],
                $response->label,
                "failed to return the correct label"
            );

            $this->assertEquals(
                $this->getExpected('select-one-item')['id'],
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
            $this->getExpected('create-one-item')['name'],
            $newRecord->name,
            "failed to return the correct names"
        );

        // Update existing
        $response = $driver->executeWriteCommand($this->getCommand('update-one-item', $newRecord->id));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $deletedRecord = $response->getSet();

        $this->assertInstanceOf('Spider\Base\Collection', $deletedRecord, 'failed to return a Record');
        $this->assertEquals(
            $this->getExpected('update-one-item')['name'],
            $deletedRecord->name,
            "failed to return the correct names"
        );

        // Delete That one
        $response = $driver->executeWriteCommand($this->getCommand('delete-one-item', $newRecord->id));

        $this->assertInstanceOf('Spider\Drivers\Response', $response, 'failed to return a Response Object');
        $deletedRecord = $response->getSet();

        $this->assertEquals($this->getExpected('delete-one-item'), $deletedRecord, "failed to delete");

        // And try to get it again
        $response = $driver->executeReadCommand($this->getCommand('select-deleted-item', $newRecord->id));

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
            $driver = $this->driver();
            $driver->open();
            $driver->startTransaction();

            $driver->executeWriteCommand($this->getCommand('create-transaction-item'));

            $driver->stopTransaction(false);

            $response = $driver->executeReadCommand($this->getCommand('select-transaction-item'));

            $this->assertFalse($response->has('name'), "the rollback did not properly work");
            $driver->close();
        });

        $this->specify("it commits properly on transactional graph", function () {
            $driver = $this->driver();
            $driver->open();
            $driver->startTransaction();

            $driver->executeWriteCommand($this->getCommand('create-transaction-item'));

            $driver->stopTransaction(true);

            $response = $driver->executeReadCommand($this->getCommand('select-transaction-item'));
            $response = $response->getSet();

            $this->assertEquals(
                $this->getExpected('select-transaction-item')['name'],
                $response->name,
                "the commit did not properly work"
            );

            // Delete That one
            $driver->runWriteCommand($this->getCommand('delete-transaction-item'));

            $driver->close();
        });

        $this->specify("it throws an Exception on multiple transaction", function () {
            $driver = $this->driver();
            $driver->open();
            $driver->startTransaction();
            $driver->startTransaction();
            $driver->close();
        }, ['throws' => new InvalidCommandException()]);

        $this->specify("it throws an Exception when a non existing transaction is stopped", function () {
            $driver = $this->driver();
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

            $response = [1,2];

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
        $driver = $this->driver();

        $response = $this->buildSingleRawResponse();

        $consistent = $driver->formatAsSet($response);
        $this->assertInstanceOf('Spider\Base\Collection', $consistent, 'Set formatting did not work for single entry');
        $this->assertEquals('dylan', $consistent->name, "name wasn't properly populated");
        $this->assertEquals('user', $consistent->label, "label wasn't properly populated");
        $this->assertTrue(is_array($consistent->meta), 'failed to populate meta');

        // test multiple results
        $response = $this->buildTwoRawResponses();
        $consistent = $driver->formatAsSet($response);

        $this->assertTrue(is_array($consistent), 'the formatted response is not an array');

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[0], 'Set formating did not return Collections');
        $this->assertTrue(is_array($consistent[0]->meta), 'failed to populate meta');
        $this->assertEquals('user', $consistent[0]->label, "label wasn't properly populated");
        $this->assertEquals('dylan', $consistent[0]->name, "name wasn't properly populated");

        $this->assertInstanceOf('Spider\Base\Collection', $consistent[1], 'Set formating did not return Collections');
        $this->assertTrue(is_array($consistent[1]->meta), 'failed to populate meta');
        $this->assertEquals('user', $consistent[1]->label, "label wasn't properly populated");
        $this->assertEquals('nicole', $consistent[1]->name, "title wasn't properly populated");
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
                $this->getExpected('select-one-item')['id'],
                $consistent->id,
                "incorrect id found"
            );
            $this->assertEquals(
                $this->getExpected('select-one-item')['label'],
                $consistent->label,
                "incorrect label found"
            );
            $consistent->id = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected label", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeReadCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();
            $this->assertEquals(
                $this->getExpected('select-one-item')['id'],
                $consistent->id,
                "incorrect id found"
            );
            $this->assertEquals(
                $this->getExpected('select-one-item')['label'],
                $consistent->label,
                "incorrect label found"
            );

            $consistent->label = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new ModifyingProtectedValueException]);

        $this->specify("it throws an Exception when a modifying protected meta", function () {
            $driver = $this->driver();
            $driver->open();
            $response = $driver->executeReadCommand($this->getCommand('select-one-item'));
            $consistent = $response->getSet();
            $this->assertEquals(
                $this->getExpected('select-one-item')['id'],
                $consistent->id,
                "incorrect id found"
            );
            $this->assertEquals(
                $this->getExpected('select-one-item')['label'],
                $consistent->label,
                "incorrect label found"
            );

            $metaKey = $this->getMetaKey();
            $consistent->meta()->$metaKey = 100; // should throw an error

            $driver->close();
        }, ['throws'=> new ModifyingProtectedValueException]);
    }

    public function testMakeProcessor()
    {
        $driver = $this->driver();
        $this->assertInstanceOf(
            'Spider\Commands\Languages\ProcessorInterface',
            $driver->makeProcessor(),
            "failed to return a language processor"
        );
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
     * Expected is a single array with: id, name, label
     * @return array
     */
    abstract public function selectOneItem();

    /**
     * Command selects exactly two records
     * Expected is two arrays, each with: id, name, label
     * @return array
     */
    abstract public function selectTwoItems();

    /**
     * Command selects exactly one record by id that does not exist
     * Expected is an empty array
     * @param $id
     * @return array
     */
    abstract public function selectDeletedItem($id);

    /**
     * Command creates a single record with a name
     * Expected is a single array with: name, label
     * @return array
     */
    abstract public function createOneItem();

    /**
     * Command updates a single item by id, changing the name
     * Expected is a single array with: name, label
     * @param $id
     * @return array
     */
    abstract public function updateOneItem($id);

    /**
     * Command deletes a single item by id
     * Expected is an empty array
     * @param $id
     * @return array
     */
    abstract public function deleteOneItem($id);

    /**
     * Command creates a single item with: name
     *
     * The name set must be predictable so
     * it can be deleted in the other transaction properties
     *
     * Expected is a single array with: label, name
     * @return array
     */
    abstract public function createTransactionItem();

    /**
     * Command selects the single item created in `createTransactionItem()`
     * Expected is a single array with: name
     * @return array
     */
    abstract public function selectTransactionItem();

    /**
     * Command deletes the item created by `createTransactionItem()`
     * Expected is an empty array
     * @return array
     */
    abstract public function deleteTransactionItem();

    /**
     * Builds and returns an array with a single record in
     * the driver's native response format
     * @return array
     */
    abstract public function buildSingleRawResponse();

    /**
     * Builds and returns an array with two records in
     * the driver's native response format
     * @return array
     */
    abstract public function buildTwoRawResponses();

    /**
     * Returns the name of a meta property used by the driver
     * @return string
     */
    abstract public function getMetaKey();
}
