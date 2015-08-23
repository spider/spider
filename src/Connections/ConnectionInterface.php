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
     * @return DriverInterface
     */
    public function open();

    /**
     * Closes database connection
     * @return DriverInterface
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
     * @return void
     */
    public function setDriver(DriverInterface $driver);
}
