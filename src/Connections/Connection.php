<?php
namespace Spider\Connections;

use Michaels\Manager\Traits\ManagesItemsTrait;
use Spider\Commands\CommandInterface;
use Spider\Drivers\DriverInterface;
use Spider\Graphs\Graph;

/**
 * Facilitates two-way communication with a driver store
 * @package Spider\Test\Unit\Connections
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
     * @param array $properties Credentials and configuration
     */
    public function __construct(DriverInterface $driver, array $properties = [])
    {
        $this->initManager($properties);
        $this->driver = $driver;
    }

    /**
     * Connects to the database
     */
    public function open()
    {
        $this->driver->setCredentials($this->getAll()); // from given properties
        return $this->driver->open();
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
     * @internal param CommandInterface $sendCommand
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
