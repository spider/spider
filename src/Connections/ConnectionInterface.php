<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Contracts\ManagesItemsInterface;
use Michaels\Spider\Commands\CommandInterface;
use Michaels\Spider\Drivers\DriverInterface;

/**
 * Facilitates two-way communication with a data-store
 * @package Michaels\Spider\Test\Unit\Connections
 */
interface ConnectionInterface extends ManagesItemsInterface
{
    /**
     * Connects to the database
     */
    public function open();

    /**
     * Closes database connection
     */
    public function close();

    /**
     * Returns the properties array
     * @return array
     */
    public function getProperties();

    /**
     * Updates the entire properties array
     *
     * @param array $properties
     */
    public function setProperties(array $properties);

    /**
     * Returns the class name of the active driver
     * @return string
     */
    public function getDriverName();

    /**
     * Returns the instance of the driver
     * @return DriverInterface
     */
    public function getDriver();

    /**
     * Updates the driver instance
     *
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver);

    /**
     * Passes to driver: executes a Query or read command
     *
     * @param CommandInterface $query
     * @return array|Graph|Record
     */
    public function executeReadCommand(CommandInterface $query);

    /**
     * Passes to driver: executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return array|Graph|Record|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command);

    /**
     * Passes to driver: executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runReadCommand(CommandInterface $query);

    /**
     * Passes to driver: executes a write command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command);
}
