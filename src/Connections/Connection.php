<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Traits\ManagesItemsTrait;
use Michaels\Spider\Drivers\DriverInterface;

/**
 * Facilitates two-way communication with a driver store
 * @package Michaels\Spider\Test\Unit\Connections
 */
class Connection implements ConnectionInterface
{
    /** @inherits from Michaels\Manager:
     *      init(), add(), get(), getAll(), exists(), has(), set(),
     *      remove(), clear(), toJson, isEmpty(), __toString()
     */
    use ManagesItemsTrait;

    /** @var  DriverInterface Instance of the driver */
    protected $driver;

    /**
     * Constructs a new connection with driver and properties
     *
     * @param DriverInterface $driver
     * @param array           $properties Credentials, host, and the like
     */
    public function __construct(DriverInterface $driver, array $properties)
    {
        $this->driver = $driver;
        $this->initManager($properties);
    }

    /**
     * Returns the properties array
     * @return array
     */
    public function getProperties()
    {
        return $this->getAll();
    }

    /**
     * Updates the entire properties array
     *
     * @param array $properties
     */
    public function setProperties(array $properties)
    {
        $this->reset($properties);
    }

    /**
     * Returns the class name of the active driver
     * @return string
     */
    public function getDriverName()
    {
        return get_class($this->driver);
    }

    /**
     * Returns the instance of the driver
     * @return DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Updates the driver instance
     *
     * @param DriverInterface $driver
     */
    public function setDriver(DriverInterface $driver)
    {
        $this->driver = $driver;
    }
}
