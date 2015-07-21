<?php
namespace Spider\Drivers\Gremlin;

use brightzone\rexpro\Connection;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Graphs\Record as SpiderRecord;
use Spider\Commands\CommandInterface;


/**
 * Driver for Gremlin Server
 * @package Michaels\Spider\Drivers\Gremlin
 */
class Driver extends AbstractDriver implements DriverInterface
{
    /**
     * @var string server hostname. Defaults to "localhost"
     */
    protected $hostname = "localhost";

    /**
     * @var int server port. Defaults to 8182.
     */
    protected $port = 8182;

    /**
     * @var string Database name or otherwise known as graph name. Defaults to "graph"
     */
    public $graph = "graph";

    /**
     * @var string The traversal object to use. Defaults to "g"
     */
    public $traversal = "g";

    /**
     * Create a new instance with a client
     *
     * @param array $properties an array of the properties to set for this class
     *
     * @return void
     */
    public function __construct(array $properties)
    {
        parent::__construct($properties);
        $this->client = new Connection();
    }

    /**
     * Open a database connection
     *
     * @return Driver $this
     */
    public function open()
    {
        $this->client->open($this->hostname, $this->graph);
        return $this;
    }

    /**
     * Close the database connection
     *
     * @return Driver $this
     */
    public function close()
    {
        $this->client->close();
        return $this;
    }

    /**
     * Executes a Query or read command
     *
     * @param CommandInterface $query
     *
     * @return array|Record|Graph
     */
    public function executeReadCommand(CommandInterface $query)
    {
        try {
            $response = $this->client->send($query->getScript());
        } catch(\Exception $e) {
            //Check for empty return error from server.
            if (($e instanceof \brightzone\rexpro\ServerException) && ($e->getCode() == 204)) {
                $response = [];
            } else {
                throw $e;
            }
        }

        if (is_array($response)) {
            return $this->mapResponse($response);
        }

        return $response;
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     *
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        return $this->executeReadCommand($command);
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     *
     * @return $this
     */
    public function runReadCommand(CommandInterface $query)
    {
        try {
            $this->client->send($query->getScript());
        } catch(\Exception $e) {
            //Check for empty return error from server.
            if (!($e instanceof \brightzone\rexpro\ServerException) || ($e->getCode() != 204)) {
                throw $e;
            }
        }
        return $this;
    }


    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface $command
     *
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command)
    {
        return $this->runReadCommand($command);
    }

    /**
     * Map a raw response to a SpiderResponse
     *
     * @param array $response
     *
     * @return SpiderRecord
     */
    protected function mapResponse(array $response)
    {
        if (count($response) == 1) {
            return $this->arrayToSpiderRecord($response[0]);
        }

        // We have an empty array
        if (empty($response)) {
            return $response;
        }

        // For multiple records, map each to a Record
        array_walk($response, function (&$array) {
            $array = $this->arrayToSpiderRecord($array);
        });
        return $response;
    }


    /**
     * Hydrate a SpiderRecord from an OrientRecord
     *
     * @param array $row a single row from result set to map.
     *
     * @return SpiderRecord
     */
    protected function arrayToSpiderRecord(array $row)
    {
        // Or we map a single record to a Spider Record
        $spiderRecord = new SpiderRecord();
        $properties = [];
        foreach($row['properties'] as $key => $value)
        {
            $properties[$key] = $value[0]['value'];
        }

        $spiderRecord->add([
            'id' => $row['id'],
            'label' => $row['label'],
            'properties' => $properties,
        ]);
        return $spiderRecord;
    }
}