<?php
namespace Spider\Drivers\Gremlin;

use Spider\Base\Collection;
use Spider\Commands\BaseBuilder;
use Spider\Commands\CommandInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;
use Spider\Exceptions\FormattingException;
use Spider\Exceptions\InvalidCommandException;
use Spider\Exceptions\NotSupportedException;
use brightzone\rexpro\Connection;

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
     * @var array The supported languages and their processors
     */
    protected $languages = [
        'gremlin' => '\Spider\Commands\Gremlin\Processor',
        'cypher' => '\Spider\Commands\Cypher\Processor',
    ];

    /**
     * @var \brightzone\rexpro\Connection The client library this driver uses to communicate with the DB
     */
    protected $client;

    /**
     * Create a new instance with a client
     *
     * @param array $properties an array of the properties to set for this class
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
        //multiple open scenario is handled by the client.
        $this->client->open($this->hostname . ':' . $this->port, $this->graph);
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
     * @param CommandInterface|BaseBuilder $query
     * @return Response
     * @throws \Exception
     * @throws \brightzone\rexpro\ServerException
     */
    public function executeReadCommand($query)
    {
        if ($query instanceof BaseBuilder) {
            throw new NotSupportedException("There are currently no processors for gremlin/cypher.");
        } elseif (!$this->isSupportedLanguage($query->getScriptLanguage())) {
            throw new NotSupportedException(__CLASS__ . " does not support " . $query->getScriptLanguage());
        }

        try {
            $response = $this->client->send($query->getScript());
        } catch (\Exception $e) {
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
     * @param CommandInterface|BaseBuilder $command
     *
     * @return Response
     */
    public function executeWriteCommand($command)
    {
        return $this->executeReadCommand($command);
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $query
     * @return $this
     * @throws \Exception
     * @throws \brightzone\rexpro\ServerException
     */
    public function runReadCommand($query)
    {
        $this->executeReadCommand($query);
        return $this;
    }


    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $command
     *
     * @return $this
     */
    public function runWriteCommand($command)
    {
        return $this->runReadCommand($command);
    }

    /**
     * Map a raw response to a SpiderResponse
     *
     * @param array $response
     *
     * @return array|Collection
     */
    protected function mapResponse(array $response)
    {
        if (count($response) == 1) {
            return $this->arrayToCollection($response[0]);
        }

        // We have an empty array
        if (empty($response)) {
            return $response;
        }

        // For multiple records, map each to a Record
        array_walk($response, function(&$array) {
            $array = $this->arrayToCollection($array);
        });
        return $response;
    }


    /**
     * Hydrate a Collection from an Gremlin response
     *
     * @param array $row a single row from result set to map.
     *
     * @return Collection
     */
    protected function arrayToCollection(array $row)
    {
        // Or we map a single record to a Spider Record
        $collection = new Collection();

        //If we're in a classic vertex/edge scenario lets do the following:
        if (isset($row['properties'])) {
            foreach ($row['properties'] as $key => $value) {
                $collection->add($key, $value[0]['value']);
            }

            foreach ($row as $key => $value) {
                if ($key != "properties") {
                    $collection->add('meta.' . $key, $value);
                }
            }
            $collection->add([
                'id' => $collection->meta()->id,
                'label' => $collection->meta()->label,
            ]);
            $collection->protect('id');
            $collection->protect('label');
            $collection->protect('meta');
        } else {
            //in any other situation lets just map directly to the collection.
            $collection->add($row);
        }

        return $collection;
    }

    /**
     * Opens a transaction
     *
     * @throws InvalidCommandException
     */
    public function startTransaction()
    {
        if ($this->inTransaction) {
            throw new InvalidCommandException("A Transaction already exists. You can not nest transactions");
        }
        $this->client->transactionStart();
        $this->inTransaction = true;
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (true) or a rollback (false)
     *
     * @return bool|void
     * @throws InvalidCommandException
     */
    public function stopTransaction($commit = true)
    {
        if (!$this->inTransaction) {
            throw new InvalidCommandException("No transaction was started");
        }
        $this->client->transactionStop($commit);
        $this->inTransaction = false;
    }

    /**
     * Format a raw response to a set of collections
     * This is for cases where a set of Vertices or Edges is expected in the response
     *
     * @param mixed $response the raw DB response
     * @return Response Spider consistent response
     * @throws FormattingException
     */
    public function formatAsSet($response)
    {
        if (!empty($response) && $this->responseFormat($response) !== self::FORMAT_SET) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }
        return $this->mapResponse($response);
    }

    /**
     * Format a raw response to a tree of collections
     * This is for cases where a set of Vertices or Edges is expected in tree format from the response
     *
     * @param mixed $response the raw DB response
     * @return void
     * @throws NotSupportedException
     */
    public function formatAsTree($response)
    {
        throw new NotSupportedException(__FUNCTION__ . "is not currently supported for the Gremlin Driver");
    }

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     * @return Response Spider consistent response
     * @throws FormattingException
     */
    public function formatAsPath($response)
    {
        if (!empty($response) && $this->responseFormat($response) !== self::FORMAT_PATH) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }

        foreach ($response as &$path) {
            $path = $this->formatAsSet($path['objects']);
        }
        return $response;
    }

    /**
     * Format a raw response to a scalar
     * This is for cases where a scalar result is expected
     *
     * @param mixed $response the raw DB response
     * @return Response Spider consistent response
     * @throws FormattingException
     */
    public function formatAsScalar($response)
    {
        if (!empty($response) && $this->responseFormat($response) !== self::FORMAT_SCALAR) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
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
        if (!is_array($response)) {
            return self::FORMAT_CUSTOM;
        }

        if (isset($response[0]) && count($response) == 1 && !is_array($response[0])) {
            return self::FORMAT_SCALAR;
        }

        if (isset($response[0]['id'])) {
            return self::FORMAT_SET;
        }

        if (isset($response[0]['objects'])) {
            return self::FORMAT_PATH;
        }
        //@todo support tree.

        return self::FORMAT_CUSTOM;
    }
}
