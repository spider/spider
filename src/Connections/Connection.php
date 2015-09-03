<?php
namespace Spider\Connections;

use Michaels\Manager\Manager as ConfigManager;
use Spider\Base\Collection;
use Spider\Base\ConfigurableInterface;
use Spider\Base\ConfigurableTrait;
use Spider\Drivers\DriverInterface;
use Spider\Integrations\Events\DispatcherInterface;
use Spider\Integrations\Events\UsesEventsTrait;

/**
 * Facilitates two-way communication with a driver store
 * @package Spider\Test\Unit\Connections
 */
class Connection extends Collection implements ConnectionInterface, ConfigurableInterface
{
    use ConfigurableTrait, UsesEventsTrait;

    /** @var  DriverInterface Instance of the driver */
    protected $driver;

    protected $driverAliases = [
        'orientdb' => 'Spider\Drivers\OrientDB\Driver',
        'gremlin' => 'Spider\Drivers\Gremlin\Driver',
        'neo4j' => 'Spider\Drivers\Neo4J\Driver',
    ];

    /**
     * Constructs a new connection with driver and properties
     *
     * @param DriverInterface|string $driver
     * @param array $driverConfig Credentials and configuration
     * @param ConfigManager|Manager $optionalConfig
     * @param DispatcherInterface $events
     */
    public function __construct(
        $driver,
        array $driverConfig = [],
        $optionalConfig = null,
        DispatcherInterface $events = null)
    {
        /* Configure the connection */

        /* Optional dependencies */
        $this->setConfigManager($optionalConfig);
        $this->setDispatcher($events);

        /* Connection Properties */
        // Were we passed all the properties through the first argument?
        $config = (is_array($driver) ? $driver : $driverConfig);
        $this->initManager($config);

        /* Setup the driver */
        if (is_string($driver)) {
            $this->driverFromString($driver);

        } elseif ($driver instanceof DriverInterface) {
            $this->driver = $driver;

        } elseif (isset($config['driver'])) {
            if (is_string($config['driver'])) {
                $this->driverFromString($config['driver']);
            } elseif ($config['driver'] instanceof DriverInterface) {
                $this->driver = $config['driver'];
            }
        }
    }

    /**
     * Connects to the database
     */
    public function open()
    {
        $this->driver->setProperties($this->getAll()); // from given credentials properties
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
     * @return \Spider\Drivers\Response
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
     * Create a driver from classname
     * @param string $driver
     */
    protected function driverFromString($driver)
    {
        // As an alias
        if (isset($this->driverAliases[$driver])) {
            $driverClass = $this->driverAliases[$driver];
            $this->driver = new $driverClass([], $this->config());

            // As a classname
        } else {
            $this->driver = new $driver([], $this->config());
        }
    }

    /**
     * Executes a Command
     *
     * This is the R in CRUD
     *
     * @param CommandInterface|BaseBuilder $query
     * @return \Spider\Drivers\Response
     */
    public function executeCommand($query)
    {
        return $this->driver->executeCommand($query);
    }

    /**
     * Runs a Command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $command
     * @return $this
     */
    public function runCommand($command)
    {
        $this->driver->runCommand($command);
        return $this;
    }

    /**
     * Opens a transaction
     *
     * @return bool|null
     */
    public function startTransaction()
    {
        return $this->driver->startTransaction();
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (true) or a rollback (false)
     *
     * @return bool|null
     */
    public function stopTransaction($commit = true)
    {
        return $this->driver->stopTransaction($commit);
    }
}
