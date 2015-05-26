<?php
namespace Michaels\Spider\Drivers\OrientDB;

use Michaels\Spider\Drivers\DriverInterface;
use PhpOrient\PhpOrient;

/**
 * Class OrientDriver
 * @package Michaels\Spider\Drivers\OrientDB
 */
class OrientDriver implements DriverInterface, DriverInterface
{

    public function __construct()
    {
        $this->client = new PhpOrient();
    }

    public function connect($properties)
    {
        $this->client->configure($properties->getAll());
        $this->client->connect();
    }

    public function listDatabases()
    {
        return $this->client->dbList();
    }

    public function open($database)
    {
        return $this->client->dbOpen($database);
    }

    public function close()
    {
        return $this->client->dbClose();
    }

    public function statement($statement)
    {
        return $this->client->query($statement);
    }
}
