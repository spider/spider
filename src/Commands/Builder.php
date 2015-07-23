<?php
namespace Spider\Commands;

use InvalidArgumentException;
use Spider\Connections\ConnectionInterface;

/**
 * Fluent Command Builder with optional connected driver
 */
class Builder
{
    /** @var ConnectionInterface Valid connection containing a driver */
    protected $connection;

    /** @var ProcessorInterface Valid, Driver-Specific Command Processor to process Command Bag */
    protected $processor;

    /** @var Bag The CommandBag with command parameters */
    protected $bag;

    /** @var Command The processed command ready for the driver to execute */
    protected $command;

    /**
     * A map of operators and conjunctions
     * These signs on the left are can be used in `where` constraints and such
     * @var array
     */
    public $operators = [
        '=' => Bag::COMPARATOR_EQUAL,
        '>' => Bag::COMPARATOR_GT,
        '<' => Bag::COMPARATOR_LT,
        '<=' => Bag::COMPARATOR_LE,
        '>=' => Bag::COMPARATOR_GE,
        '<>' => Bag::COMPARATOR_NE,

        'AND' => Bag::CONJUNCTION_AND,
        'OR' => Bag::CONJUNCTION_OR
    ];

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
    )
    {
        $this->processor = $processor;
        $this->connection = $connection;
        $this->bag = $bag ?: new Bag();
    }

    /**
     * Add a `retrieve` clause to the current Command Bag
     *
     * @param null $projections Specific fields to retrieve (defaults to *)
     * @return $this
     */
    public function retrieve($projections = null)
    {
        $this->bag->command = Bag::COMMAND_RETRIEVE;
        $this->setProjections($projections);
        return $this;
    }

    /**
     * Add a `select` clause to the current Command Bag
     *
     * Alias of retrieve
     *
     * @param null $projections
     * @return Builder
     */
    public function select($projections = null)
    {
        return $this->retrieve($projections);
    }

    /**
     * Add specific projections to the current Command Bag
     * @param $projections
     * @return Builder
     */
    public function only($projections)
    {
        $this->setProjections($projections);
        return $this;
    }

    /**
     * Add `retrieve` clause to the current Command Bag for a single record
     * @param string|int $id The id of the record
     * @return Builder
     */
    public function record($id)
    {
        return $this->from($id);
    }

    /**
     * Add `retrieve` clause to the current Command Bag for a single record
     * Alias of `record()`
     *
     * @param string|int $id The id of the record
     * @return Builder
     */
    public function byId($id)
    {
        return $this->record($id);
    }

    /**
     * Set the target in the current Command Bag
     * @param $from
     * @return $this
     */
    public function from($from)
    {
        $this->bag->target = $from;
        return $this;
    }

    /**
     * Add a single or multiple `where` constraint to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @param string $conjunction From the `self::$operators` array
     * @return $this
     */
    public function where($property, $value = null, $operator = '=', $conjunction = 'AND')
    {
        if (is_array($property)) {
            if (is_array($property[0])) { // We were handed an array of constraints
                foreach ($property as $constraint) {
                    $this->where(
                        $constraint[0], // property
                        $constraint[2] ?: $operator, // operator, default =
                        $constraint[1], // value
                        isset($constraint[3]) ? $constraint[3] : $conjunction // conjunction, default AND
                    );
                }
                return $this;
            }

            $this->where(
                $property[0], // property
                $property[2] ?: $operator, // operator, default =
                $property[1], // value
                isset($property[3]) ? $property[3] : $conjunction // conjunction, default AND
            );
            return $this;
        }

        $this->bag->where[] = [
            $property,
            $this->signToConstant($operator), // convert to constant
            $value,
            $this->signToConstant($conjunction) // convert to constant
        ];

        return $this;
    }

    /**
     * Add a `where` clause with an `OR` conjunction to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @return $this
     */
    public function orWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'OR');
    }

    /**
     * Add a `where` clause with an `AND` conjunction to the current Command Bag
     *
     * @param string $property Field name
     * @param mixed $value Value matched against
     * @param string $operator From the `self::$operators` array
     * @return $this
     */
    public function andWhere($property, $value = null, $operator = '=')
    {
        return $this->where($property, $value, $operator, 'AND');
    }

    /**
     * Set the result limit in the current Command Bag
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->bag->limit = $limit;
        return $this;
    }

    /**
     * Set which field to group results by in the current Command Bag
     * @param $fields
     * @return $this
     */
    public function groupBy($fields)
    {
        $fields = $this->csvToArray($fields);
        $this->bag->groupBy = $fields;
        return $this;
    }

    /**
     * Set which fields to order results by in the current Command Bag
     * @param $fields
     * @return $this
     */
    public function orderBy($fields)
    {
        $fields = $this->csvToArray($fields);
        $this->bag->orderBy = $fields;
        return $this;
    }

    /**
     * Return results in ascending order
     * @return $this
     */
    public function asc()
    {
        $this->bag->orderAsc = true;
        return $this;
    }

    /**
     * Return results in descending order
     * @return $this
     */
    public function desc()
    {
        $this->bag->orderAsc = false;
        return $this;
    }

    /**
     * Clear the current Command Bag
     * @param null $properties
     */
    public function clear($properties = null)
    {
        $this->bag = new Bag($properties);
        $this->command = null;
    }

    /**
     * Dispatch a command
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
            return $this->dispatchCommand($command);
        }

        if (is_string($command)) {
            return $this->dispatchCommand(new Command($command));
        }

        throw new InvalidArgumentException("`command()` only accepts strings or instances of `CommandInterface`");
    }

    /**
     * Dispatch a retrieve command with no limit.
     * Return all the results
     * @return mixed Command results
     */
    public function all()
    {
        $this->bag->limit = false; // We want all records
        return $this->dispatchCommand();
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * @return mixed Command results
     */
    public function one()
    {
        $this->bag->limit = 1;
        return $this->dispatchCommand();
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

    /**
     * Dispatch a command through the Connection
     *
     * If no instance of CommandInterface is provided, then the
     * current Command Bag is processed via the Command Processor
     *
     * @param CommandInterface|null $command
     * @return mixed Results from the command
     */
    protected function dispatchCommand(CommandInterface $command = null)
    {
        $command = $command ?: $this->getCommand();

        $this->connection->open();
        $results = $this->connection->executeReadCommand($command);
        $this->connection->close();

        return $results;
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

    /**
     * Return the current Command Bag
     * @return Bag
     */
    public function getCommandBag()
    {
        return $this->bag;
    }

    /**
     * Process the current Command Bag through the
     * current Command Processor
     */
    protected function buildCommand()
    {
        $this->command = $this->processor->process($this->bag);
    }

    /**
     * Set the projection fields in the current Command Bag
     *
     * This is used by `only()`, `select()`, and others. A projection is
     * a field affected by the current command. Like `SELECT fieldname` in SQL
     *
     * @param $projections
     * @return $this
     */
    protected function setProjections($projections)
    {
        if (is_null($projections)) {
            $this->bag->projections = [];
            return $this;
        }

        $this->bag->projections = $this->csvToArray($projections, true);
        return $this;
    }

    /**
     * Turns a Comma Seperated Sting into an array. Used to set projections.
     *
     * If $throwException is not null|false, an exception will be thrown with
     * the string value of $throwException
     *
     * @param $fields
     * @param bool|string $throwException `false` to throw no exception. A string message to throw exception.
     * @return array
     */
    protected function csvToArray($fields, $throwException = "Projections must be a comma-separated string or an array")
    {
        if (is_array($fields)) {
            return $fields;

        } elseif (is_string($fields)) {
            return array_map('trim', explode(",", $fields));
        }

        // We can't do anything with this value
        if ($throwException) {
            throw new InvalidArgumentException($throwException);
        }

        return $fields;
    }

    /**
     * Turns a user-inputed sign into a constant
     *
     * Used to turn things like '=' into Bag::COMPARATOR_EQUAL
     * in where constraints
     *
     * @param $sign
     * @return mixed
     */
    public function signToConstant($sign)
    {
        return $this->operators[$sign];
    }
}
