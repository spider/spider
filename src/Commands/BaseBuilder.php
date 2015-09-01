<?php
namespace Spider\Commands;

use InvalidArgumentException;
use Spider\Commands\Languages\ProcessorInterface;

/**
 * Fluent Command Builder, simple bag manipulation
 * No awareness of connection OR processor
 */
class BaseBuilder
{
    /** @var Bag The CommandBag with command parameters */
    protected $bag;

    /** @var  ProcessorInterface The current language processor */
    protected $processor;

    /** @var string The current query script */
    protected $script;

    /**
     * Creates a new instance of the Command Builder
     * With an optional language processor
     *
     * @param ProcessorInterface $processor
     * @param Bag|null $bag
     */
    public function __construct(
        ProcessorInterface $processor = null,
        Bag $bag = null
    )
    {
        $this->processor = $processor;
        $this->bag = $bag ?: new Bag();
    }

    /* Fluent Methods for building queries */
    /**
     * Add an `insert` clause to the current command bag
     * @param array $data
     * @return BaseBuilder
     */
    public function insert(array $data)
    {
        $this->bag->command = Bag::COMMAND_CREATE;

        if (isset($data[0]) && is_array($data[0])) {
            $this->bag->createCount = count($data);
        } else {
            $this->bag->createCount = 1;
        }

        $this->bag->data += $data;

        return $this;
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
        $this->projections($projections);
        return $this;
    }

    /**
     * An an `update` clause to the current command bag
     * @param array|null $properties should be in the format [props=>values, props2=>values2, ...]
     * @return $this
     */
    public function update($properties = null)
    {
        $this->bag->command = Bag::COMMAND_UPDATE;

        if (is_null($properties)) {
            return $this;
        }

        $this->data($properties);
        return $this;
    }

    /**
     * Add a `delete` clause to the current command bag
     * @return BaseBuilder
     */
    public function delete()
    {
        $this->bag->command = Bag::COMMAND_DELETE;
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
        if (!is_array($property)) {
            return $this->data([$property => $value]);
        } else {
            $this->bag->data[] = $property;
            return $this;
        }
    }

    /**
     * Set the type of the target in the current Command Bag
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->bag->target = $type;
        return $this;
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
    public function projections($projections)
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
     * Add a single or multiple `where` constraints to the Command Bag.
     *
     * This method *only* accepts a valid where array:
     *      ['field', OPERATOR, $value, CONJUNCTION]
     *
     * For operator and conjunction, be sure to use the `Bag` constants
     *      ['name', Bag::COMPARATOR_EQUAL, 'michael', CONJUNCTION_AND]
     *
     * For more flexible options, use `Builder`
     *
     * @param array $constraints
     * @return $this
     * @throws \Exception
     */
    public function constrain(array $constraints)
    {
        /* Force to multi-dimensional array */
        if (!is_array($constraints[0])) {
            $constraints = [$constraints];
        }

        /* Validate constraints */
        foreach ($constraints as $constraint) {
            if (count($constraint) !== 4) {
                throw new \Exception("Where constraint malformed. Must have four parameters. field, operator, value, conjunction");
            }

            if (!is_int($constraint[1]) || !is_int($constraint[3])) {
                throw new \Exception("Where constraint malformed. Operator and Conjunction must be constants from `Bag`.");
            }
        }

        $this->bag->where = array_merge($this->bag->where, $constraints);

        return $this;
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
     * Set which field to order results by in the current Command Bag
     * @param $field
     * @param $direction
     * @return $this
     */
    public function orderBy($field, $direction = Bag::ORDER_ASC)
    {
        $this->bag->orderBy[] = [$field, $direction];
        return $this;
    }

    /**
     * Flag the desired response as `tree`
     * @return $this
     */
    public function tree()
    {
        $this->bag->map = Bag::MAP_TREE;
        return $this;
    }

    /**
     * Flag the desired response as `path`
     * @return $this
     */
    public function path()
    {
        $this->bag->map = Bag::MAP_PATH;
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
    }

    /**
     * Return the current Command Bag
     * @return Bag
     */
    public function getBag()
    {
        return $this->bag;
    }

    /**
     * Processes the current command bag
     * @param ProcessorInterface $processor
     * @return String the script in string form
     * @throws \Exception
     */
    public function getScript(ProcessorInterface $processor = null)
    {
        return $this->script = $this->getCommand($processor)->getScript();
    }

    /**
     * Set the CommandProcessor
     * @param ProcessorInterface $processor
     */
    public function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;
    }

    /**
     * Is there a valid processor attached
     * @return bool
     */
    public function hasProcessor()
    {
        return isset($this->processor) && $this->processor instanceof ProcessorInterface;
    }

    /**
     * Processes the current command bag
     * @param ProcessorInterface $processor
     * @return Command
     * @throws \Exception
     */
    public function getCommand(ProcessorInterface $processor = null)
    {
        if ($processor) {
            $this->setProcessor($processor);
        } else {
            if (!$this->hasProcessor()) {
                throw new \Exception(
                    "`Builder` requires a valid instance of Spider\\Languages\\ProcessorInterface to build scripts"
                );
            }
        }

        $command = $this->processor->process(
            $this->getBag()
        );
        $this->script = $command->getScript();

        return $command;
    }

    /* Internals */
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
}
