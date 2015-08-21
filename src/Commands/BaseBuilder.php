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
        'IN' => Bag::COMPARATOR_IN,

        'AND' => Bag::CONJUNCTION_AND,
        'OR' => Bag::CONJUNCTION_OR,
        'XOR' => Bag::CONJUNCTION_XOR,
        'NOT' => Bag::CONJUNCTION_NOT,
    ];

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
     * @return $this|mixed
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
        return $this->getCommand($processor)->getScript();
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

        $this->script = $this->processor->process(
            $this->getBag()
        );

        return $this->script;
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
