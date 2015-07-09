<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Contracts\ManagesItemsInterface;
use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Queries\QueryInterface;

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
     * @param QueryInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(QueryInterface $query);

    /**
     * Passes to driver: executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param QueryInterface $query
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(QueryInterface $query);

    /**
     * Passes to driver: executes a read command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runReadCommand(QueryInterface $query);

    /**
     * Passes to driver: executes a write command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runWriteCommand(QueryInterface $query);
}
