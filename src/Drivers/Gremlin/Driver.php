<?php
namespace Spider\Drivers\Gremlin;

use brightzone\rexpro\Connection;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Graphs\Record as SpiderRecord;
use Spider\Commands\CommandInterface;
use Spider\Drivers\Response;
use Spider\Base\Collection;
use Spider\Exceptions\FormattingException;


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
    public function __construct(array $properties = [])
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

        return new Response(['_raw' => $response, '_driver' => $this]);
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
     * @return array
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
        $collection = new Collection();

        //If we're in a classic vertex/edge scenario lets do the following:
        if(isset($row['properties']))
        {
            foreach($row['properties'] as $key => $value)
            {
                $collection->add($key, $value[0]['value']);
            }

            foreach ($row as $key => $value)
            {
                if ($key != "properties")
                {
                    $collection->add('meta.'.$key, $value);
                }
            }
            $collection->add([
                'id' => $collection->meta()->id,
                'label' => $collection->meta()->label,
            ]);
            $collection->protect('id');
            $collection->protect('label');
            $collection->protect('meta');
        }
        else
        {
            //in any other situation lets just map directly to the collection.
            $collection->add($row);
        }

        return $collection;
    }

    /**
     * Opens a transaction
     *
     * @return bool
     */
    public function startTransaction()
    {
        $this->client->transactionStart();
        $this->inTransaction = TRUE;
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (TRUE) or a rollback (FALSE)
     *
     * @return bool
     */
    public function stopTransaction($commit = TRUE)
    {
        $this->client->transactionStop($commit);
        $this->inTransaction = FALSE;
    }

    /**
     * Format a raw response to a set of collections
     * This is for cases where a set of Vertices or Edges is expected in the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsSet($response)
    {
        if(!empty($response) && $this->responseFormat($response) !== self::FORMAT_SET)
        {
            throw new FormattingException("The response from the database was incorrectly formatted for this operation");
        }
        return $this->mapResponse($response);
    }

    /**
     * Format a raw response to a tree of collections
     * This is for cases where a set of Vertices or Edges is expected in tree format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsTree($response)
    {
        throw new \Exception(__FUNCTION__ . "is not currently supported for the Gremlin Driver");
    }

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsPath($response)
    {
        if(!empty($response) && $this->responseFormat($response) !== self::FORMAT_PATH)
        {
            throw new FormattingException("The response from the database was incorrectly formatted for this operation");
        }

        foreach($response as &$path)
        {
            $path = $this->formatAsSet($path['objects']);
        }
        return $response;
    }

    /**
     * Format a raw response to a scalar
     * This is for cases where a scalar result is expected
     *
     * @param mixed $response the raw DB response
     *
     * @return Response Spider consistent response
     */
    public function formatAsScalar($response)
    {
        if(!empty($response) && $this->responseFormat($response) !== self::FORMAT_SCALAR)
        {
            throw new FormattingException("The response from the database was incorrectly formatted for this operation");
        }
        return $response[0];
    }

    /**
     * Checks a response's format whenever possible
     *
     * @param mixed $response the response we want to get the format for
     *
     * @return int the format (FORMAT_X const) for the response
     */
    protected function responseFormat($response)
    {
        if(!is_array($response))
        {
            return self::FORMAT_CUSTOM;
        }

        if(isset($response[0]) && count($response[0]) == 1 && !is_array($response[0]))
        {
            return self::FORMAT_SCALAR;
        }

        if(isset($response[0]['id']))
        {
            return self::FORMAT_SET;
        }

        if(isset($response[0]['objects']))
        {
            return self::FORMAT_PATH;
        }
        //@todo support tree.

        return self::FORMAT_CUSTOM;
    }
}