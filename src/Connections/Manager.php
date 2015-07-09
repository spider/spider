<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Contracts\ManagesItemsInterface;
use Michaels\Manager\Exceptions\ItemNotFoundException;
use Michaels\Manager\Traits\ManagesItemsTrait;

/**
 * Manages and Builds Connections from a stored list
 * @package Michaels\Spider\Connections
 */
class Manager implements ManagesItemsInterface
{
    /**
     * @inherits from Michaels\Manager:
     *      init(), add(), get(), getAll(), exists(), has(), set(),
     *      remove(), clear(), toJson, isEmpty(), __toString()
     */
    use ManagesItemsTrait;

    /**
     * Build a new manager instance
     *
     * @param array $connections
     * @param array $config
     */
    public function __construct($connections = [], $config = [])
    {
        $items = [
            'connections' => $connections,
            'config' => $config,
        ];

        $this->initManager($items);
    }

    /**
     * Builds, Caches, and Returns a Connection, either default of other
     *
     * @param string $connectionName
     *
     * @return Connection
     * @throws \Michaels\Spider\Connections\ConnectionNotFoundException
     */
    public function make($connectionName = null)
    {
        $connectionName = $this->buildConnectionName($connectionName);
        $connection = $this->buildConnection($connectionName);

        $this->add("cache.$connectionName", $connection);
        return $connection;
    }

    /**
     * Returns cached connection or makes a new one
     *
     * @param null $connectionName
     * @return Connection|mixed
     * @throws ConnectionNotFoundException
     */
    public function fetch($connectionName = null)
    {
        $connectionName = $this->buildConnectionName($connectionName);

        if ($this->has("cache.$connectionName")) {
            return $this->get("cache.$connectionName");
        }

        return $this->make($connectionName);
    }

    /**
     * Clears connection cache
     * @return $this
     */
    public function clearCache()
    {
        $this->set('cache', []);
        return $this;
    }

    /**
     * Build and returns the actual connection object
     *
     * @param $connectionName
     * @return Connection
     * @throws \Michaels\Spider\Connections\ConnectionNotFoundException
     */
    protected function buildConnection($connectionName)
    {
        $credentials = $this->get("connections.$connectionName");
        $diverClassName = $credentials['driver'] . '\Driver';
        unset($credentials['driver']);

        return new Connection(new $diverClassName, $credentials, $this->get('config'));
    }

    /**
     * Checks for and builds the connection name
     *
     * Will return the default connection name if none is supplied
     * Will throw and exception if the connection requested does not exist
     *
     * @param $connectionName
     * @return mixed
     * @throws \Michaels\Spider\Connections\ConnectionNotFoundException
     * @todo Refactor: Exception should probably be thrown elsewhere
     */
    protected function buildConnectionName($connectionName = null)
    {
        // Set the default connection
        if (is_null($connectionName)) {
            try {
                $connectionName = $this->get('connections.default');
            } catch (ItemNotFoundException $e) {
                throw new ConnectionNotFoundException("There is no default connection set");
            }
        }

        // Set the supplied connection
        if (!$this->has("connections.$connectionName")) {
            throw new ConnectionNotFoundException("$connectionName has not been registered");
        }

        return $connectionName;
    }
}
