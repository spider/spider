<?php
namespace Spider;

use Michaels\Manager\Contracts\IocContainerInterface;
use Michaels\Manager\Contracts\IocManagerInterface;
use Michaels\Manager\IocManager;
use Michaels\Manager\Manager as BaseManager;
use Spider\Commands\Query;
use Spider\Connections\Manager as ConnectionManager;
use Spider\Exceptions\ConnectionNotFoundException;

class Spider extends Query
{
    /**
     * Global setup configuration
     * @var array
     */
    protected static $setup = [];

    /** @var array Defaults for global setup configuration, minus connections */
    protected static $defaults = [
        'integrations' => [
            'events' => 'Spider\Integrations\Events\Dispatcher',
        ],
        'errors' => [
            'not_supported' => 'fatal'
        ],
    ];

    /** @var IocManager System-wide IoC Container */
    protected static $iocContainer;

    /** @var  BaseManager Configuration for a specific instance */
    protected $config;

    /** @var IocManager IoC Container */
    protected $di;

    /* Static Factory and Global Configuration */
    /**
     * Setup global configuration
     * @param array $setup
     * @param IocContainerInterface $di
     */
    public static function setup(array $setup = [], IocContainerInterface $di = null)
    {
        static::$setup = $setup;
        static::$iocContainer = $di;
    }

    /**
     * Returns the static setup
     * @return array
     */
    public static function getSetup()
    {
        return static::$setup;
    }

    /**
     * Builds a new spider based on default or provided connection alias
     * @param null $connectionAlias
     * @return static
     */
    public static function make($connectionAlias = null)
    {
        if (!is_string($connectionAlias) && !is_null($connectionAlias)) {
            throw new \InvalidArgumentException("Spider::make() only accepts an alias for an already set connection");
        }

        return new static(static::$setup, $connectionAlias, static::$iocContainer);
    }

    /**
     * Returns static defaults (for testing)
     * @return array
     */
    public static function getDefaults()
    {
        return self::$defaults;
    }

    /* Instance Public API: Initialization */
    /**
     * Builds new Spider Instance which extends QueryBuilder
     * Holds active connection based on configuration
     *
     * @param array $config
     * @param null $connection alias of connection to set
     * @param IocManagerInterface $di
     * @throws ConnectionNotFoundException
     */
    public function __construct(array $config = [], $connection = null, IocManagerInterface $di = null)
    {
        // Setup dependencies
        $this->connections = new ConnectionManager();
        $this->config = new BaseManager();
        $this->di = $di ?: new IocManager();

        // Configure Instance
        if (!empty($config)) {
            $this->configure($config, $connection);
        }
    }

    /**
     * Configures current instance
     * @param array $config
     * @param null $connection
     * @throws ConnectionNotFoundException
     */
    public function configure(array $config = [], $connection = null)
    {
        /* Merge cascading defaults into config */
        if (empty($config)) {
            $config = $this->getDefaults();
        } else {
            /* Set Defaults Where Needed */
            foreach ($this->getDefaults() as $key => $value) {
                if (!isset($config[$key])) {
                    $config[$key] = $value;
                } elseif (is_array($value)) {
                    $config[$key] = array_merge($this->getDefaults()[$key], $config[$key]);
                }
            }
        }

        /* General Configuration */
        $general = $config;
        unset($general['connections']);
        $this->config->reset($general);

        /* Components for the IoC Manager */
        $this->di->initDI($config['integrations']);
        unset($config['integrations']);

        /* Event Dispatcher */
        $this->di->share('events'); // turns dispatcher into a cached singleton

        /* Connection Manager and Current Connection */
        if (isset($config['connections'])) {
            // Set the connection manifest
            $this->connections->reset($config['connections']);
            unset($config['connections']);

            // Optional configuration (not supported, etc)
            $this->connections->setConfigManager($config);

            // Set the Event Dispatcher in Manager
            $this->connections->setDispatcher($this->di->fetch('events'));

            // Set the current connection for the Query Builder
            parent::__construct(
                $this->connections->fetch($connection)
            );
        } else {
            throw new ConnectionNotFoundException("Spider cannot be instantiated without a connection");
        }
    }

    /* Instance Public API: Factories */
    /**
     * Produces a new connection from set credentials
     * @param null $alias
     * @return Connections\Connection
     * @throws ConnectionNotFoundException
     */
    public function connection($alias = null)
    {
        return $this->connections->make($alias);
    }

    /**
     * Produces a new query builder from set credentials
     * @param null $connection
     * @return Query
     * @throws ConnectionNotFoundException
     */
    public function querybuilder($connection = null)
    {
        return new Query($this->connections->make($connection));
    }

    /* Instance Public API: Getters and Setters */
    /* Inherits get|setConnection() */
    /**
     * Gets the current driver
     * @return Drivers\DriverInterface
     */
    public function getDriver()
    {
        return $this->connection->getDriver();
    }

    /**
     * Gets the current general configuration as an array
     * @return array
     */
    public function getConfig()
    {
        $config = $this->config->all();
        $config['connections'] = $this->connections->all();
        unset($config['connections']['cache']);

        return $config;
    }

    /**
     * Returns the IoC Manager
     * @return IocContainerInterface|IocManager
     */
    public function getDI()
    {
        return $this->di;
    }

    /**
     * Returns the Event Dispatcher
     * @return object
     */
    public function getEventDispatcher()
    {
        return $this->di->fetch('events');
    }

    /* Public API: Manage Spider */
    /**
     * Adds a connection
     * @param $name
     * @param array $details
     * @return $this
     */
    public function addConnection($name, array $details)
    {
        $this->connections->add($name, $details);
        return $this;
    }

    /**
     * Returns the current connection
     * @return Connections\ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
