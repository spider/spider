<?php
namespace Michaels\Spider\Connections;

use Michaels\Manager\Contracts\ManagesItemsInterface;
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
     * Builds and Returns a Connection, either default of other
     *
     * @param string $connectionName
     *
     * @return Connection
     * @throws \Michaels\Manager\Exceptions\ItemNotFoundException
     */
    public function make($connectionName = '')
    {
        $connectionName = ($connectionName !== '') ? $connectionName : $this->get('default');
        $properties = $this->get("connections.$connectionName");
        $diverClassName = $properties['driver'];
        unset($properties['driver']);

        return new Connection(new $diverClassName, $properties);
    }
}
