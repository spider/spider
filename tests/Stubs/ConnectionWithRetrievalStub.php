<?php
namespace Spider\Test\Stubs;

use Michaels\Manager\Contracts\ManagesItemsInterface;
use Michaels\Manager\Traits\ManagesItemsTrait;
use Spider\Commands\CommandInterface;
use Spider\Commands\Languages\OrientSQL\CommandProcessor;
use Spider\Connections\ConnectionInterface;
use Spider\Connections\Graph;
use Spider\Connections\Record;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;

/**
 * Class ConnectionStuf
 * @package Spider\Test\Stubs
 */
class ConnectionWithRetrievalStub implements ConnectionInterface
{
    /**
     * Connects to the database
     */
    public function open()
    {
        // TODO: Implement open() method.
    }

    /**
     * Closes database connection
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * Returns the properties array
     * @return array
     */
    public function getProperties()
    {
        // TODO: Implement getProperties() method.
    }

    /**
     * Updates the entire properties array
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        // TODO: Implement setProperties() method.
    }

    /**
     * Returns the class name of the active driver
     * @return string
     */
    public function getDriverName()
    {
        // TODO: Implement getDriverName() method.
    }

    /**
     * Returns the instance of the driver
     * @return DriverInterface
     */
    public function getDriver()
    {
        // TODO: Implement getDriver() method.
    }

    /**
     * Updates the driver instance
     *
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver)
    {
        // TODO: Implement setDriver() method.
    }

    /**
     * Passes to driver: executes a Query or read command
     *
     * @param CommandInterface $query
     * @return array|Graph|Record
     */
    public function executeReadCommand(CommandInterface $query)
    {
        return new Response(['_raw' => $query, '_driver' => $this]);
    }

    /**
     * Passes to driver: executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return array|Graph|Record|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        return new Response(['_raw' => $command, '_driver' => $this]);
    }

    /**
     * Passes to driver: executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runReadCommand(CommandInterface $query)
    {
        // whatever
    }

    /**
     * Passes to driver: executes a write command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command)
    {
        // TODO: Implement runWriteCommand() method.
    }

    /**
     * Opens a transaction
     *
     * @return bool
     */
    public function startTransaction()
    {
        // TODO: Implement startTransaction() method.
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (true) or a rollback (false)
     *
     * @return bool
     */
    public function stopTransaction($commit = true)
    {
        // TODO: Implement stopTransaction() method.
    }

    /**
     * Format a raw response to a set of collections
     * This is for cases where a set of Vertices or Edges is expected in the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsSet($response)
    {
        $response->formattedAsSet = true;
        return $response;
    }

    /**
     * Format a raw response to a tree of collections
     * This is for cases where a set of Vertices or Edges is expected in tree format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsTree($response)
    {
        $response->formattedAsTree = true;
        return $response;
    }

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsPath($response)
    {
        $response->formattedAsPath = true;
        return $response;
    }

    /**
     * Format a raw response to a scalar
     * This is for cases where a scalar result is expected
     *
     * @param mixed $response the raw DB response
     *
     * @return mixed Scalar value
     */
    public function formatAsScalar($response)
    {
        $response->formattedAsScalar = true;
        return $response;
    }

    /**
     * Initializes a new manager instance.
     *
     * This is useful for implementations that have their own __construct method
     * This is an alias for reset()
     *
     * @param array $items
     */
    public function initManager($items = [])
    {
        // TODO: Implement initManager() method.
    }

    /**
     * Adds a single item.
     *
     * Allow for dot notation (one.two.three) and item nesting.
     *
     * @param string $alias Key to be stored
     * @param mixed $item Value to be stored
     * @return $this
     */
    public function add($alias, $item = null)
    {
        // TODO: Implement add() method.
    }

    /**
     * Get a single item
     *
     * @param string $alias
     * @param null $fallback
     * @throws \Michaels\Manager\Exceptions\ItemNotFoundException If item not found
     * @return mixed
     */
    public function get($alias, $fallback = null)
    {
        // TODO: Implement get() method.
    }

    /**
     * Return all items as array
     *
     * @return array
     */
    public function getAll()
    {
        // TODO: Implement getAll() method.
    }

    /**
     * Return all items as array
     *
     * @return array
     */
    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * Confirm or deny that an item exists
     *
     * @param $alias
     * @return bool
     */
    public function exists($alias)
    {
        // TODO: Implement exists() method.
    }

    /**
     * Confirm or deny that an item exists
     *
     * @param $alias
     * @return bool
     */
    public function has($alias)
    {
        // TODO: Implement has() method.
    }

    /**
     * Updates an item
     *
     * @param string $alias
     * @param null $item
     *
     * @return $this
     */
    public function set($alias, $item = null)
    {
        // TODO: Implement set() method.
    }

    /**
     * Deletes an item
     *
     * @param $alias
     * @return void
     */
    public function remove($alias)
    {
        // TODO: Implement remove() method.
    }

    /**
     * Clear the manager
     * @return $this
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }

    /**
     * Reset the manager with an array of items
     *
     * @param array $items
     * @return mixed
     */
    public function reset($items)
    {
        // TODO: Implement reset() method.
    }

    /**
     * Returns json serialized representation of array of items
     * @return string
     */
    public function toJson()
    {
        // TODO: Implement toJson() method.
    }

    /**
     * Confirm that manager has no items
     * @return boolean
     */
    public function isEmpty()
    {
        // TODO: Implement isEmpty() method.
    }

    /**
     * When manager instance is used as a string, return json of items
     * @return mixed
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    /**
     * Returns a valid and preferred language processor
     * @return mixed
     */
    public function makeProcessor()
    {
        return new CommandProcessor();
    }
}
