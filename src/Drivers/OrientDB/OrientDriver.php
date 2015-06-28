<?php
namespace Michaels\Spider\Drivers\OrientDB;

use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Graphs\Graph;
use Michaels\Spider\Graphs\Record as SpiderRecord;
use Michaels\Spider\Queries\QueryInterface;
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\Record as OrientRecord;

/**
 * Driver for Native OrientDB (not using gremlin)
 * @package Michaels\Spider\Drivers\OrientDB
 */
class OrientDriver implements DriverInterface
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
        $this->client = new PhpOrient();
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
        $this->client->configure($credentials);
        $this->client->connect();
        $this->client->dbOpen($credentials['database']); // What if I *want* the cluster map?
    }

    /**
     * Close the database connection
     * @return $this
     */
    public function close()
    {
        $this->client->dbClose(); // returns int
        return $this;
    }

    /**
     * Executes a Query or read command
     *
     * @param QueryInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(QueryInterface $query)
    {
        $response = $this->client->query($query->getScript());

        if (is_array($response) || $response instanceof OrientRecord) {
            return $this->mapResponse($response);
        }

        return $response;
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param QueryInterface $query
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(QueryInterface $query)
    {
        $response = $this->client->command($query->getScript());

        if (is_array($response) || $response instanceof OrientRecord) {
            return $this->mapResponse($response);
        }

        return $response;
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runReadCommand(QueryInterface $query)
    {
        $this->client->query($query->getScript());
        return $this;
    }

    /**
     * Executes a write command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runWriteCommand(QueryInterface $query)
    {
        $this->client->command($query->getScript());
        return $this;
    }

    /**
     * Map a raw response to a SpiderResponse
     * @param $response
     * @return SpiderRecord
     */
    protected function mapResponse($response)
    {
        // If we have a solitary record, just map it
        if ($response instanceof OrientRecord) {
            return $this->orientToSpiderRecord($response);
        }

        // We have an empty array
        if (empty($response)) {
            return $response;
        }

        // For multiple records, map each to a Record
        array_walk($response, function (&$orientRecord) {
            $orientRecord = $this->orientToSpiderRecord($orientRecord);
        });
        return $response;
    }

    /**
     * Hydrate a SpiderRecord from an OrientRecord
     *
     * @param $orientRecord
     * @return SpiderRecord
     */
    protected function orientToSpiderRecord(OrientRecord $orientRecord)
    {
        // Or we map a single record to a Spider Record
        $spiderRecord = new SpiderRecord($orientRecord->getOData());
        $spiderRecord->add([
            'id' => $orientRecord->getRid()->jsonSerialize(),
            'rid' => $orientRecord->getRid(),
            'version' => $orientRecord->getVersion(),
            'oClass' => $orientRecord->getOClass(),
        ]);

        return $spiderRecord;
    }
}
