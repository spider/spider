<?php

namespace Spider\Drivers\Neo4J;

use Everyman\Neo4j\Client;
use Everyman\Neo4j\Cypher\Query;
use Spider\Base\Collection;
use Spider\Commands\BaseBuilder;
use Spider\Commands\CommandInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;
use Spider\Exceptions\FormattingException;
use Spider\Exceptions\InvalidCommandException;
use Spider\Exceptions\NotSupportedException;

class Driver extends AbstractDriver implements DriverInterface
{
    /**
     * @var string server hostname. Defaults to "localhost"
     */
    protected $hostname = "localhost";

    /**
     * @var int server port. Defaults to 8182.
     */
    protected $port = 7474;

    /**
     * @var string authentication username
     */
    public $username;

    /**
     * @var string authentication password
     */
    public $password;

    /**
     * @var Transaction the client transaction object if it is set
     */
    protected $transaction;

    /**
     * @var array The supported languages and their processors
     */
    protected $languages = [
        'cypher' => '\Spider\Commands\Languages\Cypher\CommandProcessor',
    ];

    /**
     * @var \brightzone\rexpro\Connection The client library this driver uses to communicate with the DB
     */
    protected $client;

    /**
     * Open a database connection
     *
     * @return Driver $this
     */
    public function open()
    {
        $this->client = new Client($this->hostname, $this->port);
        if (isset($this->username)) {
            $this->client->getTransport()
                ->setAuth($this->username, $this->password);
        }
        return $this;
    }

    /**
     * Close the database connection
     * @return void
     */
    public function close()
    {
        //nothing
    }

    /**
     * Executes a Query or read command
     *
     * This is the R in CRUD
     *
     * @param CommandInterface|BaseBuilder $query
     * @return Response
     * @throws NotSupportedException
     * @throws \Exception
     */
    public function executeReadCommand($query)
    {
        if ($query instanceof BaseBuilder) {
            $processor = new $this->languages['cypher'];
            $query = $query->getCommand($processor);
        } elseif (!$this->isSupportedLanguage($query->getScriptLanguage())) {
            throw new NotSupportedException(__CLASS__ . " does not support " . $query->getScriptLanguage());
        }

        $neoQuery = new Query($this->client, $query->getScript());
        if ($this->inTransaction) {
            $response = $this->transaction->addStatements($neoQuery);
        } else {
            $response = $neoQuery->getResultSet();
        }
        return new Response(['_raw' => $response, '_driver' => $this]);
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface|BaseBuilder $command
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
     * @return Driver $this
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
     * @return void
     */
    public function runWriteCommand($command)
    {
        $this->executeWriteCommand($command);
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
        $this->transaction = $this->client->beginTransaction();
        $this->inTransaction = true;
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (true) or a rollback (false)
     * @return bool|void
     * @throws InvalidCommandException
     */
    public function stopTransaction($commit = true)
    {
        if (!$this->inTransaction) {
            throw new InvalidCommandException("No transaction was started");
        }
        if ($commit) {
            $this->transaction->commit();
        } else {
            $this->transaction->rollback();
        }
        $this->inTransaction = false;
        $this->transaction = null;
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
        if (!empty($response[0]) && $this->responseFormat($response) !== self::FORMAT_SET) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }
        $return = [];

        foreach ($response as $row) {
            $return[] = $this->nodeToCollection($row[0]);
        }

        return count($return) == 1 ? $return[0] : $return;
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
     * @return array
     * @throws FormattingException
     */
    public function formatAsPath($response)
    {
        if (!empty($response[0]) && $this->responseFormat($response) !== self::FORMAT_PATH) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }
        $return = [];
        foreach ($response as $row) {
            $collection = [];
            foreach ($row[0] as $node) {
                $collection[] = $this->nodeToCollection($node);
            }
            $return[] = $collection;
        }
        return $return;
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
        if (!empty($response[0]) && $this->responseFormat($response) !== self::FORMAT_SCALAR) {
            throw new FormattingException(
                "The response from the database was incorrectly formatted for this operation"
            );
        }
        return $response[0][0];
    }

    /**
     * Hydrate a Collection from an Neo4J Node
     *
     * @param \Everyman\Neo4j\Node $row a single row from result set to map.
     * @return Collection
     */
    protected function nodeToCollection(\EveryMan\Neo4j\Node $row)
    {
        // Or we map a single record to a Spider Record
        $collection = new Collection();
        foreach ($row->getProperties() as $key => $value) {
            $collection->add($key, $value);
        }

        //handle labels
        $labels = $row->getLabels();
        if (!empty($labels)) {
            $collection->add([
                'meta.label' => $labels[0]->getName(),
                'label' => $labels[0]->getName(),
            ]);
        }

        $collection->add([
            'meta.id' => $row->getId(),
            'id' => $row->getId(),
        ]);
        $collection->protect('meta');
        $collection->protect('id');
        $collection->protect('label');

        return $collection;
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
        if (isset($response[0][0])
            && ($response[0][0] instanceof \Everyman\Neo4j\Node
                || ($response[0][0] instanceof \Everyman\Neo4j\Relationship))
        ) {
            return self::FORMAT_SET;
        }

        if (isset($response[0][0]) && $response[0][0] instanceof \Everyman\Neo4j\Path) {
            return self::FORMAT_PATH;
        }

        if (isset($response[0][0]) && count($response[0]) == 1 && !is_array($response[0][0])) {
            return self::FORMAT_SCALAR;
        }

        //@todo support tree.

        return self::FORMAT_CUSTOM;
    }
}
