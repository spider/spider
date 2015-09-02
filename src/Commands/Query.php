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
     * Dispatch a command through the Connection
     *
     * If no instance of CommandInterface is provided, then the
     * current Command Bag is processed via the Command Processor
     * @param CommandInterface|null $command
     * @return Response the DB response in SpiderResponse format
     */
    protected function dispatch($command = null)
    {
        $this->connection->open();

        if (isset($this->processor)) {
            //if the processor is defined we want to pass a Command to the driver.
            $message = $command ? $command : $this->getCommand(); // returns `Command`
        } else {
            // If not we will pass $this and let the driver decide which language to use.
            $message = $this;
        }

        $response = $this->connection->executeCommand($message);

        // Reset query and return response
        $this->clear();

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

    /* Dispatch without limits */
    /**
     * Dispatch a retrieve command with no limit.
     * Return all the results
     * @return array|Collection Results formatted as a Set
     */
    public function getAll()
    {
        $this->limit(false);
        $response = $this->getSet();

        return (is_array($response)) ? $response : [$response];
    }

    /**
     * Alias of set()
     * @return array|Collection
     */
    public function get()
    {
        return $this->getSet();
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * @return Collection Results formatted as a set with single collection
     */
    public function getOne()
    {
        $this->one();
        return $this->dispatch()->getSet();
    }


    /* Dispatch with Response formats */
    /**
     * Dispatches Command and formats results as a Set.
     * @return array|Collection Results formatted as a set
     */
    public function getSet()
    {
        return $this->dispatch()->getSet();
    }

    /**
     * Dispatches Command and formats results as a Tree.
     * @return array|Collection Results formatted as a tree
     */
    public function getTree()
    {
        $this->tree();
        return $this->dispatch()->getTree();
    }

    /**
     * Dispatches Command and formats results as a Path.
     * @return array|Collection Results formatted as a path
     */
    public function getPath()
    {
        $this->path();
        return $this->dispatch()->getPath();
    }

    /**
     * Dispatches Command and formats results as a scalar.
     * @return string|bool|int Results formatted as a scalar
     */
    public function getScalar()
    {
        $this->one();
        return $this->dispatch()->getScalar();
    }

    /**
     * Execute a command through dispatch
     * @param CommandInterface|null $command
     * @return Response
     */
    public function execute(CommandInterface $command = null)
    {
        return $this->dispatch($command);
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
