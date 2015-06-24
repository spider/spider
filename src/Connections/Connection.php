<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Traits\ManagesItemsTrait;
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
     * @param array $properties Credentials, host, and the like
     * @param array $config
     */
    public function __construct(DriverInterface $driver, array $properties, array $config = [])
    {
        $properties['config'] = $config;

        $this->initManager($properties);
        $this->driver = $driver;
    }

    /**
     * Connects to the database
     */
    public function connect()
    {
        $this->driver->connect($this->items);
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
        $response = call_user_func_array([$this->driver, $name], $args);

        if ((is_object($response) || is_array($response)) && !$response instanceof DriverInterface) {
            return $this->mapToReturnObject($response);
        }

        return $response;
    }

    /**
     * Maps the response to a Graph or Specified Return Object or native
     *
     * @param $response
     * @return Graph
     * @throws \Michaels\Manager\Exceptions\ItemNotFoundException
     */
    public function mapToReturnObject($response)
    {
        $returnObject = $this->get('config.return-object', 'graph');

        switch ($returnObject) {
            case 'native':
                return $response; // Return native response
                break;

            case 'graph':
                return $this->driver->mapToSpiderResponse($response); // Return Graph by default
                break;

            default:
                if ($this->has('config.map-method')) {
                    // Return a specified return object, mapped using custom method
                    $response = new $returnObject;
                    call_user_func([$response, $this->get('config.map-method')], $response);
                    return $response;
                    break;
                }

                // Return a specified return object, mapped using constructor
                return new $returnObject($response);
                break;
        }
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
