<?php
namespace Spider\Drivers\OrientDB;

use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\Record as OrientRecord;
use Spider\Commands\CommandInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Drivers\DriverInterface;
use Spider\Graphs\Graph;
use Spider\Graphs\Record as SpiderRecord;

/**
 * Driver for Native OrientDB (not using gremlin)
 * @package Spider\Drivers\OrientDB
 */
class Driver extends AbstractDriver implements DriverInterface
{
    /* Driver Credentials */
    /** @var  string OrientDB server hostname */
    protected $hostname;

    /** @var  int OrientDB server port */
    protected $port;

    /** @var  string OrientDB username for specified database */
    protected $username;

    /** @var  string Password for current OrientDB user */
    protected $password;

    /** @var  string Database name */
    protected $database;

    /* Internals */
    /** @var PhpOrient Language Binding */
    protected $client;

    /**
     * Create a new instance with a client
     * @param array $properties Configuration properties
     */
    public function __construct(array $properties = [])
    {
        // Populate configuration
        parent::__construct($properties);

        // Initialize the language binding client
        $this->client = new PhpOrient();
    }

    /**
     * Connect to the database using already set, internal credentials
     * @return $this
     */
    public function open()
    {
        $config = [];
        foreach ($this as $property => $value) {
            if ($property !== 'client') {
                $config[$property] = $value;
            }
        }

        $this->client->configure($config);
        $this->client->connect();
        $this->client->dbOpen($config['database']); // What if I *want* the cluster map?
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
     * @param CommandInterface $query
     * @return array|Record|Graph
     */
    public function executeReadCommand(CommandInterface $query)
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
     * @param CommandInterface $command
     * @return Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        $response = $this->client->command($command->getScript());

        if (is_array($response) || $response instanceof OrientRecord) {
            return $this->mapResponse($response);
        }

        return $response;
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runReadCommand(CommandInterface $query)
    {
        $this->client->query($query->getScript());
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
        $this->client->command($command->getScript());
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


    /**
     * Opens a transaction
     *
     * @return bool
     */
    public function startTransaction()
    {
        throw new \Exception(__FUNCTION__ . " is not currently supported for OrientDB driver");
    }


    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (TRUE) or a rollback (FALSE)
     *
     * @return bool
     */
    public function stopTransaction(boolean $commit)
    {
        throw new \Exception(__FUNCTION__ . " is not currently supported for OrientDB driver");
    }
}
