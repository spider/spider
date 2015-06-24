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
    /** @inherits from Michaels\Manager:
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
     * Builds and Returns a Connection, either default of other
     *
     * @param string $connectionName
     *
     * @return Connection
     * @throws \Michaels\Manager\Exceptions\ItemNotFoundException
     */
    public function make($connectionName = null)
    {
        $connectionName = $this->buildConnectionName($connectionName);
        $connection = $this->buildConnection($connectionName);

        $this->add("cache.$connectionName", $connection);
        return $connection;
    }

    public function buildConnection($connectionName)
    {
        $properties = $this->get("connections.$connectionName");
        $diverClassName = $properties['driver'];
        unset($properties['driver']);

        return new Connection(new $diverClassName, $properties, $this->get('config'));
    }

    /**
     * @param $connectionName
     * @return mixed
     * @throws \Michaels\Manager\Exceptions\ItemNotFoundException
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

//        return ($connectionName !== null) ? $connectionName : $this->get('connections.default');
    }

    public function clearCache()
    {
        $this->set('cache', []);
        return $this;
    }

    public function fetch($connectionName = null)
    {
        $connectionName = $this->buildConnectionName($connectionName);

        if ($this->has("cache.$connectionName")) {
            return $this->get("cache.$connectionName");
        }

        return $this->make($connectionName);
    }
}
