<?php
namespace Spider\Connections;

use Spider\Drivers\DriverInterface;

/**
 * Facilitates two-way communication with a data-store
 * @package Spider\Test\Unit\Connections
 */
interface ConnectionInterface
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
}
