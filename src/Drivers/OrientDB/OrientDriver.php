<?php
namespace Michaels\Spider\Drivers\OrientDB;

use Michaels\Spider\Drivers\DriverInterface;
use PhpOrient\PhpOrient;

/**
 * Class OrientDriver
 * @package Michaels\Spider\Drivers\OrientDB
 */
class OrientDriver implements DriverInterface
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

    public function blue($string)
    {
        echo "here" . $string;
    }
}
