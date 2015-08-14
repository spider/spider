<?php
namespace Spider\Connections;

use Spider\Base\Collection;
use Spider\Commands\CommandInterface;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;
use Spider\Graphs\Graph;

/**
 * Facilitates two-way communication with a driver store
 * @package Spider\Test\Unit\Connections
 */
class Connection extends Collection implements ConnectionInterface
{
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
     * @param DriverInterface $driver
     * @param array $configuration Credentials and configuration
     */
    public function __construct($driver, array $configuration = [])
    {
        $config = (is_array($driver) ? $driver : $configuration);
        $this->initManager($config);

        if (isset($config['driver'])) {
            if (isset($this->driverAliases[$config['driver']])) {
                $driverClass = $this->driverAliases[$config['driver']];
                $this->driver = new $driverClass();
            } else {
                $this->driver = new $config['driver']();
            }
        } else {
            $this->driver = $driver;
        }
    }

    /**
     * Connects to the database
     */
    public function open()
    {
        $this->driver->setProperties($this->getAll()); // from given properties
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
     * @param CommandInterface|BaseBuilder $query
     * @return Response
     */
    public function executeReadCommand($query)
    {
        return $this->driver->executeReadCommand($query);
    }

    /**
     * Passes to driver: executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface|BaseBuilder $command
     * @return Response
     * @internal param CommandInterface $sendCommand
     */
    public function executeWriteCommand($command)
    {
        return $this->driver->executeWriteCommand($command);
    }

    /**
     * Passes to driver: executes a read command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $query
     * @return $this
     */
    public function runReadCommand($query)
    {
        return $this->driver->runReadCommand($query);
    }

    /**
     * Passes to driver: executes a write command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $command
     * @return $this
     */
    public function runWriteCommand($command)
    {
        return $this->driver->runWriteCommand($command);
    }

    /**
     * Returns a valid and preferred language processor
     * @return mixed
     */
    public function makeProcessor()
    {
        return $this->driver->makeProcessor();
    }

    /**
     * Opens a transaction
     *
     * @return bool
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
     * @return bool
     */
    public function stopTransaction($commit = true)
    {
        return $this->driver->stopTransaction($commit);
    }

    /**
     * Format a raw response to a set of collections
     * This is for cases where a set of Vertices or Edges is expected in the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsSet($response)
    {
        return $this->driver->formatAsSet($response);
    }

    /**
     * Format a raw response to a tree of collections
     * This is for cases where a set of Vertices or Edges is expected in tree format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsTree($response)
    {
        return $this->driver->formatAsTree($response);
    }

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsPath($response)
    {
        return $this->driver->formatAsPath($response);
    }

    /**
     * Format a raw response to a scalar
     * This is for cases where a scalar result is expected
     *
     * @param mixed $response the raw DB response
     *
     * @return mixed Scalar value
     */
    public function formatAsScalar($response)
    {
        return $this->driver->formatAsScalar($response);
    }
}
