<?php
namespace Spider\Test\Stubs;

use Michaels\Manager\Traits\ManagesItemsTrait;
use Spider\Commands\CommandInterface;
use Spider\Connections\ConnectionInterface;
use Spider\Connections\Graph;
use Spider\Connections\Record;
use Spider\Drivers\DriverInterface;

/**
 * Class ConnectionStuf
 * @package Spider\Test\Stubs
 */
class ConnectionStub implements ConnectionInterface
{
    use ManagesItemsTrait;

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
        return $query;
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
        return $command;
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
}
