<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Traits\ManagesItemsTrait;
use Michaels\Spider\Commands\CommandInterface;
use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Graphs\Graph;

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
     * @param array $credentials Credentials, host, and the like
     * @param array $config
     */
    public function __construct(DriverInterface $driver, array $credentials, array $config = [])
    {
        $items = [
            'credentials' => $credentials,
            'config' => $config
        ];

        $this->initManager($items);
        $this->driver = $driver;
    }

    /**
     * Connects to the database
     */
    public function open()
    {
        return $this->driver->open($this->get('credentials'), $this->get('config'));
    }

    /**
     * Closes database connection
     */
    public function close()
    {
        return $this->driver->close();
    }

    /**
     * Passes through to driver
     *
     * @param $name
     * @param $args
     * @return Graph
     */
    public function __call($name, $args)
    {
        return call_user_func_array([$this->driver, $name], $args);
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

    /**
     * Passes to driver: executes a Query or read command
     *
     * @param CommandInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(CommandInterface $query)
    {
        return $this->driver->executeReadCommand($query);
    }

    /**
     * Passes to driver: executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return array|Record|Graph|mixed mixed values for some write commands
     * @internal param CommandInterface $query
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        return $this->driver->executeWriteCommand($command);
    }

    /**
     * Passes to driver: executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runReadCommand(CommandInterface $query)
    {
        return $this->driver->runReadCommand($query);
    }

    /**
     * Passes to driver: executes a write command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command)
    {
        return $this->driver->runWriteCommand($command);
    }
}
