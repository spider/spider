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
     * Creates a new instance of the Command Builder
     * With a LanguageProcessor and Connection
     * @param ProcessorInterface $processor
     * @param ConnectionInterface|null $connection
     * @param Bag|null $bag
     */
    public function __construct(
        ConnectionInterface $connection,
        ProcessorInterface $processor = null,
        Bag $bag = null
    ) {
        parent::__construct($bag);
        $this->processor = $processor ?: $connection->makeProcessor(); // defaults to the driver preferred
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
     * @return Response the DB response in SpiderResponse format
     */
    public function dispatch(CommandInterface $command = null)
    {
        $command = $command ?: $this->getScript(); // returns `Command`
        $this->connection->open();

        if ($command->getRw() === 'read') {
            return $this->connection->executeReadCommand($command);
        } else {
            return $this->connection->executeWriteCommand($command);
        }
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
        $response = $this->dispatch()->getSet();

        return (is_array($response)) ? $response : [$response];
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
        return $this->dispatch()->getSet();
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
        return $this->dispatch()->getSet();
    }

    /**
     * Dispatches Command and formats results as a Tree.
     * @return array|Collection Results formatted as a tree
     */
    public function tree()
    {
        parent::tree();
        return $this->dispatch()->getTree();
    }

    /**
     * Dispatches Command and formats results as a Path.
     * @return array|Collection Results formatted as a path
     */
    public function path()
    {
        parent::path();
        return $this->dispatch()->getPath();
    }

    /**
     * Dispatches Command and formats results as a scalar.
     * @return string|bool|int Results formatted as a scalar
     */
    public function scalar()
    {
        return $this->dispatch()->getScalar();
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
}
