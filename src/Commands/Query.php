<?php
namespace Spider\Commands;

use InvalidArgumentException;
use Spider\Base\Collection;
use Spider\Commands\Languages\ProcessorInterface;
use Spider\Connections\ConnectionInterface;

/**
 * Command Builder with connections and CommandProcessor
 */
class Query extends Builder
{
    /** @var ConnectionInterface Valid connection containing a driver */
    protected $connection;

    /**
     * @var string The response format desired. set, path, scalar, or tree
     * @default Bag::FORMAT_SET
     */
    protected $format;

    /**
     * Creates a new instance of the Command Builder
     * With a LanguageProcessor and Connection
     * @param ProcessorInterface $processor
     * @param ConnectionInterface|null $connection
     * @param Bag|null $bag
     */
    public function __construct(
        ProcessorInterface $processor,
        ConnectionInterface $connection,
        Bag $bag = null
    ) {
        parent::__construct($bag);
        $this->processor = $processor;
        $this->connection = $connection;
    }

    /* Fluent Methods for building queries */
    /**
     * Add a `delete` clause to the current command bag
     * @param null $record
     * @return $this|mixed
     */
    public function drop($record = null)
    {
        // Set the command bag
        parent::drop($record);

        // dispatch if a record was provided
        if (!is_null($record)) {
            return $this->dispatch();
        }

        return $this;
    }

    public function insert($data = null)
    {
        parent::create($data);

        if (is_null($data)) {
            return $this;
        }

        return $this->dispatch();
    }

    /**
     * In some cases, choose what the database sends back
     * after the operation. For instance, if deleting
     * Do you want the records affected, record
     * before, or a simple `true` for success?
     *
     * $builder->drop(3)->fromDb('AFTER')
     *
     * @note NOT IMPLEMENTED YET see PR #21
     * @param null $wanted
     * @return $this
     */
    public function fromDb($wanted = null)
    {
        $this->bag->return = (is_null($wanted)) ? true : $this->csvToArray($wanted);
        return $this;
    }

    /* Dispatch */
    /**
     * Execute a command directly from the public api
     *
     * Accepts either a CommandInterface or a string with
     * the native script which is converted to a CommandInterface
     *
     * @param $command
     * @return mixed Results from Command as SpiderResponse
     */
    public function command($command)
    {
        if ($command instanceof CommandInterface) {
            return $this->dispatch($command);
        }

        if (is_string($command)) {
            return $this->dispatch(new Command($command));
        }

        throw new InvalidArgumentException("`command()` only accepts strings or instances of `CommandInterface`");
    }

    /**
     * Dispatch a command through the Connection
     *
     * If no instance of CommandInterface is provided, then the
     * current Command Bag is processed via the Command Processor
     *
     * @param CommandInterface|null $command
     * @return mixed Results from the command as a SpiderResponse
     */
    public function dispatch(CommandInterface $command = null)
    {
        $command = $command ?: $this->getScript(); // returns `Command`
        $this->connection->open();

        if ($command->getRw() === 'read') {
            $results = $this->connection->executeReadCommand($command);
        } else {
            $results = $this->connection->executeWriteCommand($command);
        }

        // Are we requesting a specific format?
        if ($this->format) {
            // Pass the results through the appropriate formatAsX Driver method
            $formats = [
                Bag::FORMAT_SET => 'formatAsSet',
                Bag::FORMAT_SCALAR => 'formatAsScalar',
                Bag::FORMAT_PATH => 'formatAsPath',
                Bag::FORMAT_TREE => 'formatAsTree'
            ];

            return $this->connection->$formats[$this->format]($results);
            /* For tests, returns Response above with ::formattedAsSet = true; */
        }

        // Nope, return the SpiderResponse
        return $results;
    }

    /* Dispatch with limits */
    /**
     * Dispatch a retrieve command with no limit.
     * Return all the results
     * @return array|Collection Results formatted as a Set
     */
    public function all()
    {
        $this->limit(false);
        $this->setFormat(Bag::FORMAT_SET);
        return $this->dispatch();
    }

    /**
     * Alias of set()
     * @return array|Collection
     */
    public function get()
    {
        return $this->set();
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * @return Collection Results formatted as a set with single collection
     */
    public function one()
    {
        parent::first();
        $this->setFormat(Bag::FORMAT_SET);
        return $this->dispatch();
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * @return Collection Results formatted as a set with single collection
     */
    public function first()
    {
        return $this->one();
    }

    /* Dispatch with Response formats */
    /**
     * Dispatches Command and formats results as a Set.
     * @return array|Collection Results formatted as a set
     */
    public function set()
    {
        $this->setFormat(Bag::FORMAT_SET);
        return $this->dispatch();
    }

    /**
     * Dispatches Command and formats results as a Tree.
     * @return array|Collection Results formatted as a tree
     */
    public function tree()
    {
        $this->setFormat(Bag::FORMAT_TREE);
        return $this->dispatch();
    }

    /**
     * Dispatches Command and formats results as a Path.
     * @return array|Collection Results formatted as a path
     */
    public function path()
    {
        $this->setFormat(Bag::FORMAT_PATH);
        return $this->dispatch();
    }

    /**
     * Dispatches Command and formats results as a scalar.
     * @return string|bool|int Results formatted as a scalar
     */
    public function scalar()
    {
        $this->setFormat(Bag::FORMAT_SCALAR);
        return $this->dispatch();
    }

    /* Manage the Builder itself */
    /**
     * Clear the current Command Bag
     * @param array $properties
     */
    public function clear($properties = [])
    {
        parent::clear($properties);
        $this->script = null;
    }

    /**
     * Sets the desired response format
     * @param $format
     */
    protected function setFormat($format)
    {
        // For the driver to formatAsX()
        $this->format = $format;

        // Flag for the Language Processor
        $this->bag->format = $format;
    }
}
