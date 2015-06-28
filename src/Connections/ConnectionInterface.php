<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Contracts\ManagesItemsInterface;
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
}
