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
    /** @var array An array of CommandBags with command parameters */
    protected $bag;

    /** @var  ProcessorInterface The current language processor */
    protected $processor;

    /** @var string The current query script */
    protected $script;

    /**
     * Creates a new instance of the Command Builder
     * With an optional language processor
     *
     * @param ProcessorInterface|null $processor
     * @param Bag $bag
     */
    public function __construct(
        ProcessorInterface $processor = null,
        Bag $bag = null
    ) {
        $this->processor = $processor;
        $this->bag = $bag ?: new Bag();
    }

    /* Internal methods for building queries */
    /**
     * Add an `insert` clause to the current command bag
     * @param array $data
     * @return $this
     */
    public function internalCreate(array $data)
    {
        $this->addToBag('create', $data);
        return $this;
    }

    /**
     * Add a `retrieve` clause to the current Command Bag
     *
     * @param null $projections Specific fields to retrieve (defaults to *)
     * @return $this
     */
    public function internalRetrieve($projections = null)
    {
        if (is_null($projections)) {
            $this->addToBag('retrieve', []);
            return $this;
        }

        // Ensure $projects is usable
        if (!is_string($projections) && !is_array($projections)) {
            throw new InvalidArgumentException("Projections must be a comma-separated string or an array");
        }

        $this->addToBag('retrieve', $this->csvToArray($projections));
        return $this;
    }

    /**
     * An an `update` clause to the current command bag
     * @param array|null $properties should be in the format [props=>values, props2=>values2, ...]
     * @return $this
     */
    public function internalUpdate($properties = null)
    {
        $this->addToBag('update', $properties);
        return $this;
    }

    /**
     * Add a `delete` clause to the current command bag
     * @return BaseBuilder
     */
    public function internalDelete()
    {
        $this->addToBag('delete', true);
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
    public function internalWhere(array $constraints)
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

    /* Fluent methods for building queries */
    /**
     * Set the result limit in the current Command Bag
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->addToBag('limit', $limit);
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
        $this->addToBag('groupBy', $fields);
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
        $this->addToBag('orderBy', [[$field, $direction]]);
        return $this;
    }

    /**
     * Flag the desired response as `tree`
     * @return $this
     */
    public function setAsTree()
    {
        $this->addToBag('map', Bag::MAP_TREE);
        return $this;
    }

    /**
     * Flag the desired response as `path`
     * @return $this
     */
    public function setAsPath()
    {
        $this->addToBag('map', Bag::MAP_PATH);
        return $this;
    }

    /* Sub Queries */
    /**
     * Sets an alias for the current command bag
     * @param $alias
     */
//    function set($alias)
//    {
//        $keys = array_keys($this->bag);
//        $index = end($keys);
//        $this->bag[$alias] = $this->getCurrentBag();
//        unset($this->bag[$index]);
//    }
//
//    /**
//     * Returns a Command Bag by alias
//     * @param $alias
//     * @return mixed
//     */
//    function get($alias)
//    {
//        return $this->bag[$alias];
//    }

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
     * @param ProcessorInterface|null $processor
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
     * @param ProcessorInterface|null $processor
     * @return \Spider\Commands\Command
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

    /**
     * Adds a clause to the current Command Bag
     * @param $property
     * @param $value
     */
    protected function addToBag($property, $value)
    {
        $this->bag->$property = $value;
    }
}
