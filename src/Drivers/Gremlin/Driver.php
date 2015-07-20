<?php
namespace Michaels\Spider\Drivers\Gremlin;

use brightzone\rexpro\Connection;
use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Graphs\Record as SpiderRecord;
use Michaels\Spider\Queries\CommandInterface;


/**
 * Driver for Gremlin Server
 * @package Michaels\Spider\Drivers\Gremlin
 */
class Driver implements DriverInterface
{
    /**
     * @var array user-configuration passed from connection
     */
    protected $config;

    /**
     * Create a new instance with a client
     */
    public function __construct()
    {
        $this->client = new Connection();
    }

    /**
     * Open a database connection
     *
     * @param array $credentials credentials
     * @param array $config
     * @return $this
     */
    public function open(array $credentials, array $config = [])
    {
        $this->config = $config;
        $this->client->open($credentials['hostname'], $credentials['graph']);
    }

    /**
     * Close the database connection
     * @return $this
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
     * @return array|Record|Graph
     */
    public function executeReadCommand(CommandInterface $query)
    {
        try {
            $response = $this->client->send($query->getScript());
        } catch(\Exception $e) {
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
     * @return $this
     */
    public function runReadCommand(CommandInterface $query)
    {
        try {
        $this->client->send($query->getScript());
        } catch(\Exception $e) {
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
     * @return $this
     */
    public function runWriteCommand(CommandInterface $command)
    {
        return $this->runReadCommand($command);
    }

    /**
     * Map a raw response to a SpiderResponse
     * @param $response
     * @return SpiderRecord
     */
    protected function mapResponse($response)
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
     * @param $orientRecord
     * @return SpiderRecord
     */
    protected function arrayToSpiderRecord($array)
    {
        // Or we map a single record to a Spider Record
        $spiderRecord = new SpiderRecord();
        $properties = [];
        foreach($array['properties'] as $key => $value)
        {
            $properties[$key] = $value[0]['value'];
        }

        $spiderRecord->add([
            'id' => $array['id'],
            'label' => $array['label'],
            'properties' => $properties,
        ]);
        return $spiderRecord;
    }
}