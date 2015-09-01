<?php
namespace Spider\Commands;

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
     * @param ConnectionInterface $connection
     * @param Bag|null $bag
     */
    public function __construct(
        ConnectionInterface $connection,
        ProcessorInterface $processor = null,
        Bag $bag = null
    )
    {
        parent::__construct($processor, $bag);
        $this->connection = $connection;
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
        $this->connection->open();

        if (isset($this->processor)) {
            //if the processor is defined we want to pass a Command to the driver.
            $message = $command ? $command : $this->getCommand(); // returns `Command`
        } else {
            // If not we will pass $this and let the driver decide which language to use.
            $message = $this;
        }

        if ($this->bag->command === Bag::COMMAND_RETRIEVE) {
            $response = $this->connection->executeReadCommand($message);
        } else {
            $response = $this->connection->executeWriteCommand($message);
        }

        // Reset query and return response
        $this->bag = new Bag();
        return $response;
    }

    /**
     * Alias of dispatch
     * @return Response
     */
    public function go()
    {
        return $this->dispatch();
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
        $response = $this->set();

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
        $this->limit(1);
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
