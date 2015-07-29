<?php
namespace Spider\Commands;

use InvalidArgumentException;
use Spider\Commands\Languages\ProcessorInterface;
use Spider\Connections\ConnectionInterface;

/**
 * Command Builder with sugar, no awareness of connections
 */
class Query extends Builder
{
    /** @var ConnectionInterface Valid connection containing a driver */
    protected $connection;

    /** @var ProcessorInterface Valid, Driver-Specific Command Processor to process Command Bag */
    protected $processor;

    /** @var Command The processed command ready for the driver to execute */
    protected $command;

    /** @var string The response format desired. set, path, scalar, or tree  */
    protected $format = 'set';

    /**
     * Creates a new instance of the Command Builder
     *
     * @param ProcessorInterface $processor
     * @param ConnectionInterface|null $connection
     * @param Bag|null $bag
     */
    public function __construct(
        ProcessorInterface $processor,
        ConnectionInterface $connection = null,
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


    /* Execute a command with limits */
    /**
     * Dispatch a retrieve command with no limit.
     * Return all the results
     * @return mixed Command results
     */
    public function all()
    {
        parent::all();
        return $this->dispatch();
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * @return mixed Command results
     */
    public function one()
    {
        parent::one();
        return $this->dispatch();
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * Alias of `one()`
     * @return mixed Command results
     */
    public function first()
    {
        return $this->one();
    }


    /* Response formats */
    public function set()
    {
        $this->format = 'set';
        return $this;
    }

    public function tree()
    {
        $this->format = 'tree';
        return $this;
    }

    public function path()
    {
        $this->format = 'path';
        return $this;
    }

    public function scalar()
    {
        $this->format = 'scalar';
        return $this;
    }

    /* Manage the Builder itself */
    /**
     * Clear the current Command Bag
     * @param array $properties
     */
    public function clear($properties = [])
    {
        parent::clear($properties);
        $this->command = null;
    }

    /**
     * Execute a command directly from the public api
     *
     * Accepts either a CommandInterface or a string with
     * the native script which is converted to a CommandInterface
     *
     * @param $command
     * @return mixed Results from Command
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
     * @return mixed Results from the command
     */
    public function dispatch(CommandInterface $command = null)
    {
        $command = $command ?: $this->getCommand();

        $this->connection->open();
        $results = $this->connection->executeReadCommand($command);
        $this->connection->close();

        $formatMethod = "formatAs".ucfirst($this->format);

        return $results->$formatMethod();
    }

    /**
     * Return the current, processed Command
     *
     * If no command is set, build the command from current Command Bag
     * @return Command
     */
    public function getCommand()
    {
        if (!$this->command) {
            $this->buildCommand();
        }

        return $this->command;
    }

    /* Internals */
    /**
     * Process the current Command Bag through the
     * current Command Processor
     */
    protected function buildCommand()
    {
        $this->command = $this->processor->process($this->bag);
    }
}
