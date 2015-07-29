<?php
namespace Spider\Commands;

use InvalidArgumentException;
use Spider\Connections\ConnectionInterface;
use Spider\Graphs\ID as TargetID;

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
        $this->processor = $processor;
        $this->connection = $connection;
        $this->bag = $bag ?: new Bag();
    }

    /* Fluent Methods for building queries */
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
     * Add an `insert` clause to the current command bag
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        $this->bag->command = Bag::COMMAND_CREATE;

        if (isset($data[0]) && is_array($data[0])) {
            $this->bag->createCount = count($data);
        } else {
            $this->bag->createCount = 1;
        }

        $this->bag->data = $data;

        return $this->dispatch();
    }

    /**
     * An an `update` clause to the current command bag
     * @param null $property
     * @param null $value
     * @return $this
     */
    public function update($property = null, $value = null)
    {
        $this->bag->command = Bag::COMMAND_UPDATE;

        // We're just setting the command
        if (is_null($property)) {
            return $this;
        }

        // Or, We're adding a single bit of data as well
        if (!is_null($value)) {
            $this->data($property, $value);
            return $this;
        }

        // Okay, so we only have a $property. That leaves us with 2 possibilities
        // First, the $property is an array of data to be added
        if (is_array($property)) {
            $this->data($property, $value);
            return $this;

            // Second, the $property is a target
        } else {
            $this->from($property);
            return $this;
        }
    }

    /**
     * Update only the first record
     * @param $target
     * @return $this
     */
    public function updateFirst($target)
    {
        $this->bag->command = Bag::COMMAND_UPDATE;
        $this->limit(1);
        $this->from($target);

        return $this;
    }

    /**
     * Add a `delete` clause to the current command bag
     * @param null $record
     * @return $this|mixed
     */
    public function drop($record = null)
    {
        $this->bag->command = Bag::COMMAND_DELETE;

        if (!is_null($record)) {
            $this->record($record);
            return $this->dispatch();
        }

        return $this;
    }

    /**
     * Add data to the current command bag (for insert and update)
     * @param $property
     * @param null $value
     * @return $this
     */
    public function data($property, $value = null)
    {
        if (is_array($property)) {
            foreach ($property as $key => $value) {
                $this->data($key, $value);
            }
        } else {
            $this->bag->data[$property] = $value;
        }
        return $this;
    }

    /**
     * Alias of `data()`
     * @param $property
     * @param null $value
     * @return Builder
     */
    public function withData($property, $value = null)
    {
        return $this->data($property, $value);
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
     * Add a record id to the current Command Bag as target
     * @param string|int $id The id of the record
     * @return Builder
     */
    public function record($id)
    {
        if (is_array($id)) {
            $ids = array_map(function ($value) {
                return new TargetID($value);
            }, $id);

            return $this->from($ids);
        }

        return $this->from(new TargetID($id));
    }

    /**
     * Add several records as target
     * @param $ids
     * @return Builder
     */
    public function records($ids)
    {
        return $this->record($ids);
    }

    /**
     * Alias of record
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
     * @param $target
     * @return $this
     */
    public function from($target)
    {
        $this->bag->target = $target;
        return $this;
    }

    /**
     * Alias of from, used for fluency
     * @param $target
     * @return Builder
     */
    public function into($target)
    {
        return $this->from($target);
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


    /* Execute a command with limits */
    /**
     * Dispatch a retrieve command with no limit.
     * Return all the results
     * @return mixed Command results
     */
    public function all()
    {
        $this->bag->limit = false; // We want all records
        return $this->dispatch();
    }

    /**
     * Retrieve the first result by dispatching the current Command Bag.
     * @return mixed Command results
     */
    public function one()
    {
        $this->bag->limit = 1;
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
        $this->bag = new Bag($properties);
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

    /**
     * Return the current Command Bag
     * @return Bag
     */
    public function getCommandBag()
    {
        return $this->bag;
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

        // Ensure $projects is usable
        if (!is_string($projections) && !is_array($projections)) {
            throw new InvalidArgumentException("Projections must be a comma-separated string or an array");
        }

        $this->bag->projections = $this->csvToArray($projections);
        return $this;
    }

    /**
     * Turns a Comma Separated Sting into an array. Used to set projections.
     *
     * If $throwException is not null|false, an exception will be thrown with
     * the string value of $throwException
     *
     * @param $string
     * @return array
     */
    protected function csvToArray($string)
    {
        if (is_string($string)) {
            return array_map('trim', explode(",", $string));
        }

        return $string;
    }

    /**
     * Turns a user-inputted sign into a constant
     *
     * Used to turn things like '=' into Bag::COMPARATOR_EQUAL
     * in where constraints
     *
     * @param $sign
     * @return mixed
     */
    protected function signToConstant($sign)
    {
        return $this->operators[$sign];
    }
}
