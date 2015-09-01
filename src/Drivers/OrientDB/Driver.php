<?php
namespace Spider\Drivers\OrientDB;

use PhpOrient\Exceptions\PhpOrientException as ServerException;
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\Record as OrientRecord;
use Spider\Base\Collection;
use Spider\Commands\BaseBuilder;
use Spider\Commands\CommandInterface;
use Spider\Drivers\AbstractDriver;
use Spider\Drivers\DriverInterface;
use Spider\Drivers\Response;
use Spider\Exceptions\FormattingException;
use Spider\Exceptions\InvalidCommandException;
use Spider\Exceptions\NotSupportedException;

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

    /** @var  bool Is connection open, flag */
    protected $isOpen = false;

    /** @var string Messge for exception thrown at formatting error */
    protected $formatMessage = "The response from the database was incorrectly formatted for this operation";

    /** @var string Current transaction (batch) statement */
    protected $transaction = '';

    /** @var array Batch variables */
    protected $transactionVariables;

    /**
     * @var array The supported languages and their processors
     */
    protected $languages = [
        'orientSQL' => '\Spider\Commands\Languages\OrientSQL\CommandProcessor',
    ];

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

        // Flag as an open connection
        $this->isOpen = true;
    }

    /**
     * Close the database connection
     * @return $this
     */
    public function close()
    {
        if ($this->isOpen) {
            $this->client->dbClose(); // returns int
            $this->isOpen = false;
        }

        return $this;
    }

    /**
     * Opens a transaction
     * @return void
     * @throws \Exception
     */
    public function startTransaction()
    {
        if ($this->inTransaction) {
            throw new InvalidCommandException("A Transaction already exists. You can not nest transactions");
        }

        $this->inTransaction = true;
        $this->transaction = "begin\n";
    }

    /**
     * Closes a transaction
     *
     * @param bool $commit whether this is a commit (TRUE) or a rollback (FALSE)
     * @return void
     * @throws \Exception
     */
    public function stopTransaction($commit = true)
    {
        if (!$this->inTransaction) {
            throw new InvalidCommandException("No transaction was started");
        }

        if ($commit) {
            $this->endTransaction();
            $command = $this->transaction;
            $this->transaction = null;
            $this->inTransaction = false;

            $this->client->sqlBatch($command);
        }
    }

    /**
     * Finishes transaction statement and returns for testing
     * @return string
     */
    public function getTransactionForTest()
    {
        $this->endTransaction();
        return $this->transaction;
    }

    /**
     * Finishes the transaction statement
     */
    protected function endTransaction()
    {
        $this->writeTransactionStatement("commit");
        $this->writeTransactionStatement(" return " . $this->getTransactionVariables());
    }

    /**
     * Write a new clause to the transaction statement
     * @param string $statement
     */
    protected function writeTransactionStatement($statement)
    {
        $this->transaction .= $statement;
    }

    /**
     * Add a new operation to the transaction statement
     * @param $statement
     */
    protected function addTransactionStatement($statement)
    {
        $this->writeTransactionStatement(
            'LET ' . $this->incrementTransactionVariables() . ' = ' . $statement . "\n"
        );
    }

    /**
     * Increment transaction variables
     * @return string
     */
    protected function incrementTransactionVariables()
    {
        $newIndex = count($this->transactionVariables) + 1;
        $this->transactionVariables[] = "t" . (string)$newIndex;
        return 't' . (string)$newIndex;
    }

    /**
     * Get the transaction variables for the RETURN array
     * @return string
     */
    protected function getTransactionVariables()
    {
        $this->transactionVariables = array_map(function($value) {
            return '$' . $value;
        }, $this->transactionVariables);

        return "[" . implode(",", $this->transactionVariables) . "]";
    }

    /**
     * Executes a Query or read command
     *
     * @param CommandInterface|BaseBuilder $query
     * @return Response
     */
    public function executeReadCommand($query)
    {
        return $this->executeCommand($query, 'query');
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface|BaseBuilder $command
     * @return Response|null values for some write commands
     */
    public function executeWriteCommand($command)
    {
        if ($this->inTransaction) {
            $this->addTransactionStatement($command->getScript());
            return null;
        }

        /* ToDo: DELETE is very sloppy */
        /* DELETE VERTEX returns an int. DELETE returns either int or before Record */
        /* Drivers expect an empty array upon successful delete */
        /* This needs to be reconciled in a better way */
        $response = $this->executeCommand($command, 'command');

        if (strpos(strtolower($command->getScript()), "delete") === 0) {
            return new Response(['_raw' => [], '_driver' => $this]);
        }

        return $response;
    }

    /**
     * Executes actual command or query
     * @param CommandInterface|BaseBuilder $command
     * @param string $method
     * @return Response
     * @throws NotSupportedException
     * @throws \Exception
     */
    protected function executeCommand($command, $method)
    {
        if ($command instanceof BaseBuilder) {
            $processor = new $this->languages['orientSQL'];
            $command = $command->getCommand($processor);
        } elseif (!$this->isSupportedLanguage($command->getScriptLanguage())) {
            throw new NotSupportedException(__CLASS__ . " does not support " . $command->getScriptLanguage());
        }

        try {
            $response = $this->client->$method($command->getScript());
        } catch (ServerException $e) {
            // Wrap a "class doesn't exist" exception
            if (strpos($e->getMessage(), "not found in database")) {
                throw new ClassDoesNotExistException($e->getMessage());
            } else {
                throw $e;
            }
        }
        $response = $this->rawResponseToArray($response);
        return new Response(['_raw' => $response, '_driver' => $this]);
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $query
     * @return $this
     * @throws NotSupportedException
     * @throws \Exception
     */
    public function runReadCommand($query)
    {
        if ($query instanceof BaseBuilder) {
            $processor = new $this->languages['orientSQL'];
            $query = $query->getCommand($processor);
        } elseif (!$this->isSupportedLanguage($query->getScriptLanguage())) {
            throw new NotSupportedException(__CLASS__ . " does not support " . $query->getScriptLanguage());
        }

        $this->client->query($query->getScript());
        return $this;
    }

    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface|BaseBuilder $command
     * @return $this
     * @throws NotSupportedException
     * @throws \Exception
     */
    public function runWriteCommand($command)
    {
        if ($command instanceof BaseBuilder) {
            $processor = new $this->languages['orientSQL'];
            $command = $command->getCommand($processor);
        } elseif (!$this->isSupportedLanguage($command->getScriptLanguage())) {
            throw new NotSupportedException(__CLASS__ . " does not support " . $command->getScriptLanguage());
        }

        $this->client->command($command->getScript());
        return $this;
    }

    /**
     * Map a raw response to a SpiderResponse
     * @param $response
     * @return Response
     */
    protected function mapRawResponse(array $response)
    {
        // Return an empty array immediately
        if (empty($response)) {
            return $response;
        }

        // Receive array with single scalar
        if (!$response[0] instanceof OrientRecord) {
            return $response[0];
        }

        // For multiple records, map each to a Record
        array_walk($response, function(&$orientRecord) {
            $orientRecord = $this->mapOrientRecordToCollection($orientRecord);
        });

        return $response;
    }

    /**
     * Hydrate a SpiderRecord from an OrientRecord
     *
     * @param $orientRecord
     * @return Response
     */
    protected function mapOrientRecordToCollection(OrientRecord $orientRecord)
    {
        // Or we map a single record to a Spider Record
        $collection = new Collection($orientRecord->getOData());

        $collection->add([
            'id' => $orientRecord->getRid()->jsonSerialize(),
            'label' => $orientRecord->getOClass(),

            'meta.rid' => $orientRecord->getRid(),
            'meta.version' => $orientRecord->getVersion(),
            'meta.oClass' => $orientRecord->getOClass(),
        ]);

        $collection->protect('id');
        $collection->protect('label');
        $collection->protect('meta');

        return $collection;
    }

    /**
     * Ensures that an OrientDB response is an array,
     * even if only an array of one Record
     * @param $response
     * @return array
     */
    protected function rawResponseToArray($response)
    {
        if (is_array($response)) {
            return $response;
        }

        return [$response];
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
        $this->canFormat($response, self::FORMAT_SET);

        $mapped = $this->mapRawResponse($response);

        if (count($mapped) === 1) {
            return $mapped[0];
        }

        return $mapped;
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
        // TODO: Implement formatAsTree() method.
        throw new NotSupportedException(__FUNCTION__ . " is not currently supported for OrientDB driver");
    }

    /**
     * Format a raw response to a path of collections
     * This is for cases where a set of Vertices or Edges is expected in path format from the response
     *
     * @param mixed $response the raw DB response
     * @return void
     * @throws NotSupportedException
     */
    public function formatAsPath($response)
    {
        // TODO: Implement formatAsPath() method.
        throw new NotSupportedException(__FUNCTION__ . " is not currently supported for OrientDB driver");
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
        // In case we are fetching a scalar from one record with one property
        try {
            $this->canFormat($response, self::FORMAT_SCALAR);
        } catch (FormattingException $e) {
            if ($this->canBeScalar($response, $e)) {
                foreach ($response[0]->getOData() as $key => $value) {
                    return $value;
                }
            } else {
                throw $e; // Rethrow the exception
            }
        }

        // Otherwise, its a single scalar
        return $response[0];
    }

    /**
     * Ensure that a response can be formatted as desired
     * @param $response
     * @param integer $desiredFormat
     * @throws FormattingException
     */
    protected function canFormat($response, $desiredFormat)
    {
        $format = $this->responseFormat($response);
        if (!empty($response) && $format !== $desiredFormat) {
            $message = "The response from the database was incorrectly formatted for this operation";
            $exception = new FormattingException($message);
            $exception->setFormat($format);

            throw $exception;
        }
    }

    /**
     * Checks a response's format whenever possible
     *
     * @param mixed $response the response we want to get the format for
     * @return int the format (FORMAT_X const) for the response
     */
    protected function responseFormat($response)
    {
        if (!is_array($response)) {
            return self::FORMAT_CUSTOM;
        }

        if (!empty($response) && $response[0] instanceof OrientRecord) {
            return self::FORMAT_SET;
        }

        if (count($response) == 1 && !is_array($response[0])) {
            return self::FORMAT_SCALAR;
        }

        //@todo support path
        //@todo support tree.

        return self::FORMAT_CUSTOM;
    }

    /**
     * Can a response set be formatted as a scalar?
     * @param $response
     * @param FormattingException $e
     * @return bool
     */
    protected function canBeScalar($response, $e)
    {
        // returns true if all conditions are met
        return $e->getFormat() === self::FORMAT_SET
        && count($response) === 1
        && count($response[0]->getOData()) === 1;
    }
}
